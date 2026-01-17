<?php
// teacher/api/get_submission_details.php
require_once '../../middleware/auth.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

$submission_id = $_GET['submission_id'] ?? null;
if (!$submission_id) {
    echo json_encode(['error' => 'Missing ID']);
    exit;
}

// 1. Fetch Basic Info + File Path + Scores
$stmt = $pdo->prepare("
    SELECT 
        s.submission_type, s.file_path, s.score,
        a.max_score
    FROM activity_submissions s
    JOIN activities a ON s.activity_id = a.activity_id
    WHERE s.submission_id = ?
");
$stmt->execute([$submission_id]);
$sub = $stmt->fetch(PDO::FETCH_ASSOC);

if ($sub && $sub['submission_type'] === 'file') {
    echo json_encode([
        'type' => 'file',
        'file_path' => $sub['file_path'],
        'score' => $sub['score'],       // Current grade
        'max_score' => $sub['max_score'] // Limit
    ]);
    exit;
}

// 2. If QUIZ, return standard breakdown
$sql = "
    SELECT 
        q.question_text, q.points, q.question_type,
        q.correct_answer AS correct_key,
        qa.answer_text, qa.points_earned, qa.is_correct,
        opt.option_text AS selected_option_text,
        (SELECT option_text FROM quiz_question_options WHERE question_id = q.question_id AND is_correct = 1 LIMIT 1) AS correct_option_text
    FROM student_quiz_answers qa
    JOIN quiz_questions q ON qa.question_id = q.question_id
    LEFT JOIN quiz_question_options opt ON qa.selected_option_id = opt.option_id
    WHERE qa.submission_id = ?
    ORDER BY q.question_number
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$submission_id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>