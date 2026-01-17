<?php
// teacher/actions/update_grade.php

// 1. Safety
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 2. Start Session
session_start();

// 3. Database Connection
if (!file_exists('../../config/database.php')) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database config not found']);
    exit;
}
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    // 4. Auth Check
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
        throw new Exception('Unauthorized access');
    }

    // 5. Get Input
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);

    if (!$input)
        throw new Exception('No data received');

    $submission_id = $input['submission_id'] ?? null;
    $score = $input['score'] ?? null;

    // 6. Validation
    if (!$submission_id || !is_numeric($score)) {
        throw new Exception('Invalid input: Missing ID or Score');
    }

    // 7. Verify Max Score
    $stmt = $pdo->prepare("
        SELECT a.max_score 
        FROM activity_submissions s 
        JOIN activities a ON s.activity_id = a.activity_id 
        WHERE s.submission_id = ?
    ");
    $stmt->execute([$submission_id]);
    $max = $stmt->fetchColumn();

    if ($max === false)
        throw new Exception("Submission #$submission_id not found");
    if ($score < 0 || $score > $max)
        throw new Exception("Score must be between 0 and $max");

    $pdo->beginTransaction();

    // 8. Update Submission Status (The specific activity record)
    $update = $pdo->prepare("
        UPDATE activity_submissions 
        SET score = ?, status = 'graded', graded_at = NOW() 
        WHERE submission_id = ?
    ");
    $update->execute([$score, $submission_id]);

    // 9. MANUAL TRIGGER REPLACEMENT: Sync to Main Gradebook (grades table)
    // Find the student enrollment ID and component ID based on the submission
    $infoStmt = $pdo->prepare("
        SELECT 
            se.id as subject_enrollment_id, 
            gc.component_id 
        FROM activity_submissions sub
        JOIN activities a ON sub.activity_id = a.activity_id
        JOIN subject_assignments sa ON a.assignment_id = sa.assignment_id
        JOIN grading_components gc ON gc.assignment_id = a.assignment_id 
            AND gc.quarter = a.quarter 
            AND gc.component_type = a.component_type 
            AND gc.item_number = a.item_number
        JOIN enrollments e ON e.student_id = sub.student_id AND e.section_id = sa.section_id
        JOIN subject_enrollments se ON se.enrollment_id = e.id AND se.subject_id = sa.subject_id
        WHERE sub.submission_id = ?
    ");
    $infoStmt->execute([$submission_id]);
    $info = $infoStmt->fetch(PDO::FETCH_ASSOC);

    // If we found the matching gradebook slot, update it
    if ($info) {
        $gradeStmt = $pdo->prepare("
            INSERT INTO grades (subject_enrollment_id, component_id, score) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE score = VALUES(score)
        ");
        $gradeStmt->execute([$info['subject_enrollment_id'], $info['component_id'], $score]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>