<?php
require_once '../../middleware/auth.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

$school_year_id = $_GET['school_year_id'] ?? null;
$section_id = $_GET['section_id'] ?? null;
$subject_id = $_GET['subject_id'] ?? null;
$quarter = $_GET['quarter'] ?? null;

if (!$school_year_id || !$section_id || !$subject_id || !$quarter) {
    echo json_encode([]);
    exit;
}

try {
    // First, get the assignment_id
    $stmt = $pdo->prepare("
        SELECT assignment_id 
        FROM subject_assignments 
        WHERE subject_id = ? 
        AND section_id = ? 
        AND school_year_id = ?
        AND is_active = 1
        LIMIT 1
    ");
    $stmt->execute([$subject_id, $section_id, $school_year_id]);
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assignment) {
        echo json_encode(['error' => 'No assignment found']);
        exit;
    }

    $assignment_id = $assignment['assignment_id'];

    // Get grading components (WW, PT, QA items with max scores)
    $stmt = $pdo->prepare("
        SELECT component_id, component_type, item_number, max_score, description
        FROM grading_components
        WHERE assignment_id = ?
        AND quarter = ?
        ORDER BY component_type, item_number
    ");
    $stmt->execute([$assignment_id, $quarter]);
    $components = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organize components by type
    $ww_components = [];
    $pt_components = [];
    $qa_component = null;

    foreach ($components as $comp) {
        if ($comp['component_type'] === 'ww') {
            $ww_components[] = $comp;
        } elseif ($comp['component_type'] === 'pt') {
            $pt_components[] = $comp;
        } elseif ($comp['component_type'] === 'qa') {
            $qa_component = $comp;
        }
    }

    // Get students enrolled in this subject
    $stmt = $pdo->prepare("
        SELECT 
            s.student_id,
            CONCAT(s.last_name, ', ', s.first_name) AS full_name,
            se.id as subject_enrollment_id
        FROM students s
        INNER JOIN enrollments e ON s.student_id = e.student_id
        INNER JOIN subject_enrollments se ON e.id = se.enrollment_id
        WHERE e.section_id = ?
        AND e.school_year_id = ?
        AND se.subject_id = ?
        AND se.is_enrolled = 1
        AND e.status = 'active'
        ORDER BY s.last_name, s.first_name
    ");
    $stmt->execute([$section_id, $school_year_id, $subject_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // For each student, get their grades
    $result = [];

    foreach ($students as $student) {
        $student_data = [
            'student_id' => $student['student_id'],
            'subject_enrollment_id' => $student['subject_enrollment_id'],
            'full_name' => $student['full_name'],
            'ww' => [],
            'ww_max' => [],
            'pt' => [],
            'pt_max' => [],
            'qa' => null,
            'qa_max' => null
        ];

        // Get all grades for this student
        $stmt = $pdo->prepare("
            SELECT gc.component_type, gc.item_number, gc.max_score, 
                   g.score, gc.component_id
            FROM grading_components gc
            LEFT JOIN grades g ON gc.component_id = g.component_id 
                AND g.subject_enrollment_id = ?
            WHERE gc.assignment_id = ?
            AND gc.quarter = ?
            ORDER BY gc.component_type, gc.item_number
        ");
        $stmt->execute([$student['subject_enrollment_id'], $assignment_id, $quarter]);
        $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($grades as $grade) {
            if ($grade['component_type'] === 'ww') {
                $student_data['ww'][] = [
                    'component_id' => $grade['component_id'],
                    'score' => $grade['score'],
                    'max_score' => $grade['max_score']
                ];
            } elseif ($grade['component_type'] === 'pt') {
                $student_data['pt'][] = [
                    'component_id' => $grade['component_id'],
                    'score' => $grade['score'],
                    'max_score' => $grade['max_score']
                ];
            } elseif ($grade['component_type'] === 'qa') {
                $student_data['qa'] = [
                    'component_id' => $grade['component_id'],
                    'score' => $grade['score'],
                    'max_score' => $grade['max_score']
                ];
            }
        }

        // Get computed grade if exists
        $stmt = $pdo->prepare("
            SELECT final_grade, ww_percentage, pt_percentage, qa_percentage
            FROM computed_grades
            WHERE subject_enrollment_id = ?
            AND quarter = ?
        ");
        $stmt->execute([$student['subject_enrollment_id'], $quarter]);
        $computed = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($computed) {
            $student_data['final_grade'] = $computed['final_grade'];
            $student_data['ww_percentage'] = $computed['ww_percentage'];
            $student_data['pt_percentage'] = $computed['pt_percentage'];
            $student_data['qa_percentage'] = $computed['qa_percentage'];
        }

        $result[] = $student_data;
    }

    // Return both students data and component structure
    echo json_encode([
        'students' => $result,
        'components' => [
            'ww' => $ww_components,
            'pt' => $pt_components,
            'qa' => $qa_component
        ],
        'assignment_id' => $assignment_id
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>