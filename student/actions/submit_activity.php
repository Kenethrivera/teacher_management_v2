<?php
// student/actions/submit_activity.php
// FIXED: Multiple Choice Array Key Mapping

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $activity_id = $_POST['activity_id'] ?? null;
    $type = $_POST['type'] ?? null;
    $student_id = $_SESSION['student_id'] ?? null;

    if (!$student_id) {
        $stmt = $pdo->prepare("SELECT student_id FROM students WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $student_id = $stmt->fetchColumn();
    }

    if (!$activity_id || !$student_id)
        throw new Exception("Missing Data");

    $pdo->beginTransaction();

    // Check duplicate
    $checkStmt = $pdo->prepare("SELECT submission_id FROM activity_submissions WHERE activity_id = ? AND student_id = ?");
    $checkStmt->execute([$activity_id, $student_id]);
    if ($checkStmt->fetch())
        throw new Exception("Already submitted.");

    // --- FILE SUBMISSION ---
    if ($type === 'file') {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed.");
        }

        $uploadDir = '../../uploads/student_submissions/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);

        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $filename = "act{$activity_id}_std{$student_id}_" . time() . "." . $ext;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $filename)) {
            throw new Exception("Failed to save file.");
        }

        $sql = "INSERT INTO activity_submissions (activity_id, student_id, submission_type, file_path, status, submitted_at) 
                VALUES (?, ?, 'file', ?, 'submitted', NOW())";
        $pdo->prepare($sql)->execute([$activity_id, $student_id, $filename]);

    }
    // --- QUIZ SUBMISSION ---
    elseif ($type === 'quiz') {
        $answers = json_decode($_POST['answers'], true);
        if (!is_array($answers))
            throw new Exception("Invalid answers.");

        $totalScore = 0;

        // 1. Create Submission
        $subSql = "INSERT INTO activity_submissions (activity_id, student_id, submission_type, status, submitted_at) 
                   VALUES (?, ?, 'quiz', 'graded', NOW())";
        $pdo->prepare($subSql)->execute([$activity_id, $student_id]);
        $submission_id = $pdo->lastInsertId();

        // 2. Get Questions
        $qStmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE activity_id = ?");
        $qStmt->execute([$activity_id]);
        $dbQuestions = $qStmt->fetchAll(PDO::FETCH_ASSOC);
        $qMap = [];
        foreach ($dbQuestions as $q)
            $qMap[$q['question_id']] = $q;

        // 3. Get Correct Options (THE FIX IS HERE)
        // We select question_id FIRST so it becomes the Key of the array
        $optStmt = $pdo->prepare("
            SELECT question_id, option_id FROM quiz_question_options 
            WHERE is_correct = 1 AND question_id IN (SELECT question_id FROM quiz_questions WHERE activity_id = ?)
        ");
        $optStmt->execute([$activity_id]);
        $correctOptions = $optStmt->fetchAll(PDO::FETCH_KEY_PAIR); // [question_id => option_id]

        // 4. Grade
        $ansStmt = $pdo->prepare("
            INSERT INTO student_quiz_answers (submission_id, question_id, answer_text, selected_option_id, is_correct, points_earned)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($answers as $ans) {
            $qId = $ans['q_id'];
            $userVal = trim($ans['val']);
            $question = $qMap[$qId] ?? null;
            if (!$question)
                continue;

            $isCorrect = 0;
            $selectedOptionId = null;
            $answerText = $userVal;

            if ($question['question_type'] === 'multiple_choice') {
                $selectedOptionId = $userVal;
                $answerText = null;
                // Correct Logic: Check if $correctOptions has this Q_ID and matches User_ID
                if (isset($correctOptions[$qId]) && $correctOptions[$qId] == $selectedOptionId) {
                    $isCorrect = 1;
                }
            } else {
                // Text/TrueFalse
                if (strcasecmp($userVal, $question['correct_answer']) == 0) {
                    $isCorrect = 1;
                }
            }

            $points = $isCorrect ? floatval($question['points']) : 0;
            $totalScore += $points;

            $ansStmt->execute([$submission_id, $qId, $answerText, $selectedOptionId, $isCorrect, $points]);
        }

        // 5. Update Score
        $pdo->prepare("UPDATE activity_submissions SET score = ? WHERE submission_id = ?")->execute([$totalScore, $submission_id]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>