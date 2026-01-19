<?php
// student/actions/submit_activity.php

// 1. DISABLE ERROR PRINTING
error_reporting(0);
ini_set('display_errors', 0);

// 2. Set JSON Header
header('Content-Type: application/json');

session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $activity_id = $_POST['activity_id'] ?? null;
    $type = $_POST['type'] ?? null;
    $student_id = $_SESSION['student_id'] ?? null;

    // Fetch student_id if missing
    if (!$student_id) {
        $stmt = $pdo->prepare("SELECT student_id FROM students WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $student_id = $stmt->fetchColumn();
    }

    if (!$activity_id || !$student_id) {
        throw new Exception("Missing Data");
    }

    $pdo->beginTransaction();

    // --- FILE SUBMISSION ---
    if ($type === 'file') {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed. Error code: " . ($_FILES['file']['error'] ?? 'Unknown'));
        }

        // CRITICAL FIX: Use the system temporary directory
        // This is the only guaranteed writable path on Render Free Tier
        $uploadDir = sys_get_temp_dir() . '/';

        // Generate unique filename
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $filename = "act{$activity_id}_std{$student_id}_" . time() . "." . $ext;
        $targetPath = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
            throw new Exception("Failed to save file to disk (Permissions Error).");
        }

        /* NOTE: Since files in /tmp are deleted when the server restarts, 
           this is for DEMO purposes. For a real production app, 
           you would upload this file to AWS S3 or Google Cloud Storage here.
           But for this project, /tmp is sufficient to pass the submission.
        */

        // Save filename to Database
        $sql = "INSERT INTO activity_submissions (activity_id, student_id, submission_type, file_path, status, submitted_at) 
                VALUES (?, ?, 'file', ?, 'submitted', NOW())
                ON DUPLICATE KEY UPDATE file_path = VALUES(file_path), status = 'submitted', submitted_at = NOW()";

        $pdo->prepare($sql)->execute([$activity_id, $student_id, $filename]);

    }
    // --- QUIZ SUBMISSION ---
    elseif ($type === 'quiz') {
        $answers = json_decode($_POST['answers'], true);
        if (!is_array($answers)) {
            throw new Exception("Invalid answers format.");
        }

        $totalScore = 0;

        // 1. Create or Update Submission Record
        $subSql = "INSERT INTO activity_submissions (activity_id, student_id, submission_type, status, submitted_at) 
                   VALUES (?, ?, 'quiz', 'graded', NOW())
                   ON DUPLICATE KEY UPDATE status = 'graded', submitted_at = NOW()";
        $pdo->prepare($subSql)->execute([$activity_id, $student_id]);

        $idStmt = $pdo->prepare("SELECT submission_id FROM activity_submissions WHERE activity_id = ? AND student_id = ?");
        $idStmt->execute([$activity_id, $student_id]);
        $submission_id = $idStmt->fetchColumn();

        // 2. Get Questions
        $qStmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE activity_id = ?");
        $qStmt->execute([$activity_id]);
        $dbQuestions = $qStmt->fetchAll(PDO::FETCH_ASSOC);
        $qMap = [];
        foreach ($dbQuestions as $q) {
            $qMap[$q['question_id']] = $q;
        }

        // 3. Get Correct Options
        $optStmt = $pdo->prepare("
            SELECT question_id, option_id FROM quiz_question_options 
            WHERE is_correct = 1 AND question_id IN (SELECT question_id FROM quiz_questions WHERE activity_id = ?)
        ");
        $optStmt->execute([$activity_id]);
        $correctOptions = $optStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // 4. Grade Answers
        $pdo->prepare("DELETE FROM student_quiz_answers WHERE submission_id = ?")->execute([$submission_id]);

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
                if (isset($correctOptions[$qId]) && $correctOptions[$qId] == $selectedOptionId) {
                    $isCorrect = 1;
                }
            } else {
                if (strcasecmp($userVal, $question['correct_answer']) == 0) {
                    $isCorrect = 1;
                }
            }

            $points = $isCorrect ? floatval($question['points']) : 0;
            $totalScore += $points;

            $ansStmt->execute([$submission_id, $qId, $answerText, $selectedOptionId, $isCorrect, $points]);
        }

        // 5. Update Total Score
        $pdo->prepare("UPDATE activity_submissions SET score = ? WHERE submission_id = ?")->execute([$totalScore, $submission_id]);

        // 6. Trigger Sync to Class Record Logic (Manual Trigger Replacement)
        $infoStmt = $pdo->prepare("
            SELECT se.id as subject_enrollment_id, gc.component_id 
            FROM activity_submissions sub
            JOIN activities a ON sub.activity_id = a.activity_id
            JOIN subject_assignments sa ON a.assignment_id = sa.assignment_id
            JOIN grading_components gc ON gc.assignment_id = a.assignment_id 
                AND gc.quarter = a.quarter AND gc.component_type = a.component_type AND gc.item_number = a.item_number
            JOIN enrollments e ON e.student_id = sub.student_id AND e.section_id = sa.section_id
            JOIN subject_enrollments se ON se.enrollment_id = e.id AND se.subject_id = sa.subject_id
            WHERE sub.submission_id = ?
        ");
        $infoStmt->execute([$submission_id]);
        $info = $infoStmt->fetch(PDO::FETCH_ASSOC);

        if ($info) {
            $gradeStmt = $pdo->prepare("
                INSERT INTO grades (subject_enrollment_id, component_id, score) VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE score = VALUES(score)
            ");
            $gradeStmt->execute([$info['subject_enrollment_id'], $info['component_id'], $totalScore]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Submitted Successfully!']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>