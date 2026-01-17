<?php
// student/api/get_grades.php
// PURPOSE: Fetches the student's report card, pivoting quarters into columns

// 1. Safety: Turn off error printing so it doesn't break JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// 2. Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode([]);
    exit;
}

try {
    // 3. Ensure we have the Student ID
    $student_id = $_SESSION['student_id'] ?? null;
    if (!$student_id) {
        $stmt = $pdo->prepare("SELECT student_id FROM students WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $student_id = $stmt->fetchColumn();
        if ($student_id) {
            $_SESSION['student_id'] = $student_id;
        } else {
            echo json_encode([]); // No student profile found
            exit;
        }
    }

    // 4. Fetch Grades
    // Added 'cg.release_type' to the SELECT list to fix the error
    $sql = "
        SELECT 
            sub.subject_name,
            CONCAT(t.last_name, ', ', t.first_name) as teacher_name,
            cg.quarter,
            cg.final_grade,
            cg.is_released,
            cg.release_type  
        FROM students s
        JOIN enrollments e ON s.student_id = e.student_id
        JOIN subject_enrollments se ON e.id = se.enrollment_id
        JOIN subjects sub ON se.subject_id = sub.subject_id
        LEFT JOIN subject_assignments sa ON sa.subject_id = sub.subject_id AND sa.section_id = e.section_id
        LEFT JOIN teachers t ON sa.teacher_id = t.teacher_id
        LEFT JOIN computed_grades cg ON se.id = cg.subject_enrollment_id
        WHERE s.student_id = ? 
        AND e.status = 'active'
        ORDER BY sub.subject_name, cg.quarter
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id]);
    $raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Process Data (Pivot)
    $reportCard = [];

    foreach ($raw as $row) {
        $subj = $row['subject_name'];

        // Initialize row if new subject
        if (!isset($reportCard[$subj])) {
            $reportCard[$subj] = [
                'subject' => $subj,
                'teacher' => $row['teacher_name'] ?? 'TBA',
                'q1' => '-',
                'is_released_q1' => false,
                'type_q1' => null,
                'q2' => '-',
                'is_released_q2' => false,
                'type_q2' => null,
                'q3' => '-',
                'is_released_q3' => false,
                'type_q3' => null,
                'q4' => '-',
                'is_released_q4' => false,
                'type_q4' => null,
                'final' => '-'
            ];
        }

        // Fill data if quarter exists and is released
        if ($row['quarter'] && $row['is_released'] == 1) {
            $qKey = 'q' . $row['quarter'];
            $reportCard[$subj][$qKey] = $row['final_grade'];
            $reportCard[$subj]['is_released_' . $qKey] = true;
            $reportCard[$subj]['type_' . $qKey] = $row['release_type'];
        }
    }

    echo json_encode(array_values($reportCard));

} catch (Exception $e) {
    // Return a clean JSON error instead of crashing
    echo json_encode(['error' => $e->getMessage()]);
}
?>