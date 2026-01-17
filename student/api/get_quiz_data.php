<?php
// student/api/get_quiz_data.php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$activity_id = $_GET['activity_id'] ?? null;

if (!$activity_id) {
    echo json_encode([]);
    exit;
}

try {
    // 1. Fetch Questions
    // NOTE: We do NOT select 'correct_answer' here to prevent cheating
    $qStmt = $pdo->prepare("
        SELECT question_id, question_number, question_text, question_type, points
        FROM quiz_questions
        WHERE activity_id = ?
        ORDER BY question_number ASC
    ");
    $qStmt->execute([$activity_id]);
    $questions = $qStmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch Options for Multiple Choice
    // We get ALL options for the relevant questions
    $optStmt = $pdo->prepare("
        SELECT option_id, question_id, option_text
        FROM quiz_question_options
        WHERE question_id IN (
            SELECT question_id FROM quiz_questions WHERE activity_id = ?
        )
        ORDER BY option_order ASC
    ");
    $optStmt->execute([$activity_id]);
    $allOptions = $optStmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Attach options to their respective questions
    foreach ($questions as &$q) {
        $q['options'] = [];
        if ($q['question_type'] === 'multiple_choice') {
            $q['options'] = array_values(array_filter($allOptions, function ($opt) use ($q) {
                return $opt['question_id'] === $q['question_id'];
            }));
        }
    }

    echo json_encode($questions);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>