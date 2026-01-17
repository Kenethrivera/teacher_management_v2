<?php
// teacher/actions/update_grade.php

// 1. Safety: Turn off HTML error display so it doesn't break JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 2. Start Session
session_start();

// 3. Database Connection
// Ensure this path is correct. If your config folder is in the root, this is correct.
if (!file_exists('../../config/database.php')) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database config not found']);
    exit;
}
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    // 4. Auth Check (Manual check, no external file needed)
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
        throw new Exception('Unauthorized access');
    }

    // 5. Get Input
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);

    if (!$input) {
        throw new Exception('No data received');
    }

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

    if ($max === false) {
        throw new Exception("Submission #$submission_id not found");
    }

    if ($score < 0 || $score > $max) {
        throw new Exception("Score must be between 0 and $max");
    }

    // 8. Update Database
    $update = $pdo->prepare("
        UPDATE activity_submissions 
        SET score = ?, status = 'graded', graded_at = NOW() 
        WHERE submission_id = ?
    ");
    $update->execute([$score, $submission_id]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Catch ANY error and return it as JSON
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>