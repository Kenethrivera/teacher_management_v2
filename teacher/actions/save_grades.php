<?php
// teacher/actions/save_grades.php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['assignment_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Update Max Scores (in case teacher changed them)
    $compStmt = $pdo->prepare("UPDATE grading_components SET max_score = ? WHERE component_id = ?");

    // Helper to loop types
    $types = ['ww', 'pt'];
    foreach ($types as $type) {
        foreach ($data['components'][$type] as $comp) {
            $compStmt->execute([$comp['max_score'], $comp['component_id']]);
        }
    }
    if (isset($data['components']['qa'])) {
        $qa = $data['components']['qa'];
        $compStmt->execute([$qa['max_score'], $qa['component_id']]);
    }

    // 2. Save Student Grades
    $gradeStmt = $pdo->prepare("
        INSERT INTO grades (subject_enrollment_id, component_id, score) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE score = VALUES(score)
    ");

    $computedStmt = $pdo->prepare("
        INSERT INTO computed_grades (subject_enrollment_id, quarter, final_grade, computed_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE final_grade = VALUES(final_grade), computed_at = NOW()
    ");

    foreach ($data['students'] as $student) {
        // Save individual scores
        foreach ($student['grades'] as $grade) {
            if ($grade['score'] !== null && $grade['score'] !== '') {
                $gradeStmt->execute([
                    $student['subject_enrollment_id'],
                    $grade['component_id'],
                    $grade['score']
                ]);
            }
        }

        // Save computed final grade
        $computedStmt->execute([
            $student['subject_enrollment_id'],
            $data['quarter'],
            $student['final_grade']
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>