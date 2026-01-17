<?php
// teacher/actions/create_activity.php
session_start();
header('Content-Type: application/json');
require_once '../../config/database.php';

// 1. Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// 2. Get Teacher ID
$stmt = $pdo->prepare("SELECT teacher_id FROM teachers WHERE user_id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$teacher_id = $stmt->fetchColumn();

if (!$teacher_id) {
    echo json_encode(['success' => false, 'message' => 'Teacher profile not found.']);
    exit;
}

// 3. Get Payload
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
    exit;
}

// 4. Extract Basic Fields
$school_year_id = $input['school_year_id'] ?? null;
$subject_id = $input['subject_id'] ?? null;
$section_ids = $input['section_ids'] ?? []; // This is now an ARRAY
$quarter = $input['quarter'] ?? null;
$component_type = $input['component_type'] ?? null;
$item_number = $input['item_number'] ?? null; // NOTE: This might collide if we loop. Best to calculate per section.
$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
$activity_type = $input['activity_type'] ?? 'file';
$max_score = $input['max_score'] ?? 100;
$due_date = !empty($input['due_date']) ? $input['due_date'] : null;
$questions = $input['questions'] ?? [];

// Validate Required
if (!$school_year_id || !$subject_id || empty($section_ids) || !$title) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields (Title, Subject, or Sections).']);
    exit;
}

try {
    $pdo->beginTransaction();

    // === LOOP THROUGH EACH SECTION ===
    foreach ($section_ids as $sec_id) {

        // A. Find the Assignment ID for this specific Section + Subject + Teacher
        $assignStmt = $pdo->prepare("
            SELECT assignment_id FROM subject_assignments 
            WHERE teacher_id = ? AND subject_id = ? AND section_id = ? AND school_year_id = ?
        ");
        $assignStmt->execute([$teacher_id, $subject_id, $sec_id, $school_year_id]);
        $assignment_id = $assignStmt->fetchColumn();

        if (!$assignment_id) {
            // Skip this section if the teacher isn't assigned to it
            continue;
        }

        // B. Check or Auto-Generate Item Number
        // If user manually set Item #1, we try to use it. If it exists, we error out OR auto-increment.
        // Let's stick to strict checking to avoid mess:
        $checkStmt = $pdo->prepare("
            SELECT activity_id FROM activities 
            WHERE assignment_id = ? AND quarter = ? AND component_type = ? AND item_number = ?
        ");
        $checkStmt->execute([$assignment_id, $quarter, $component_type, $item_number]);
        if ($checkStmt->fetch()) {
            throw new Exception("Activity Item #$item_number already exists for one of the selected sections.");
        }

        // C. Insert Activity
        $sql = "INSERT INTO activities 
                (assignment_id, quarter, component_type, item_number, title, description, activity_type, max_score, due_date, is_published, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $assignment_id,
            $quarter,
            $component_type,
            $item_number,
            $title,
            $description,
            $activity_type,
            $max_score,
            $due_date
        ]);
        $activity_id = $pdo->lastInsertId();

        // D. Insert Quiz Questions (Only if Quiz)
        if ($activity_type === 'quiz' && !empty($questions)) {
            $qStmt = $pdo->prepare("INSERT INTO quiz_questions (activity_id, question_number, question_text, question_type, points, correct_answer) VALUES (?, ?, ?, ?, ?, ?)");
            $optStmt = $pdo->prepare("INSERT INTO quiz_question_options (question_id, option_text, option_order, is_correct) VALUES (?, ?, ?, ?)");

            foreach ($questions as $index => $q) {
                $qStmt->execute([$activity_id, $index + 1, $q['text'], $q['type'], $q['points'], $q['correctAnswer']]);
                $question_id = $pdo->lastInsertId();

                if ($q['type'] === 'multiple_choice' && !empty($q['options'])) {
                    foreach ($q['options'] as $optIdx => $optText) {
                        if (trim($optText) === '')
                            continue;
                        $isCorrect = (isset($q['correctAnswer']) && (string) $optIdx === (string) $q['correctAnswer']) ? 1 : 0;
                        $optStmt->execute([$question_id, $optText, $optIdx + 1, $isCorrect]);
                    }
                }
            }
        }

        // E. Sync with Class Record (DB Trigger handles grading_component creation, but we assume it runs)
        // Manually fetch the component_id to init grades
        $compStmt = $pdo->prepare("
            SELECT component_id FROM grading_components 
            WHERE assignment_id = ? AND quarter = ? AND component_type = ? AND item_number = ?
        ");
        $compStmt->execute([$assignment_id, $quarter, $component_type, $item_number]);
        $component_id = $compStmt->fetchColumn();

        if ($component_id) {
            // Get Students in this section
            $studStmt = $pdo->prepare("
                SELECT se.id AS subject_enrollment_id 
                FROM subject_enrollments se
                JOIN enrollments e ON se.enrollment_id = e.id
                WHERE e.section_id = ? AND se.subject_id = ? AND e.school_year_id = ? AND e.status = 'active'
            ");
            $studStmt->execute([$sec_id, $subject_id, $school_year_id]);
            $students = $studStmt->fetchAll(PDO::FETCH_ASSOC);

            // Init empty grades
            $gradeStmt = $pdo->prepare("INSERT IGNORE INTO grades (subject_enrollment_id, component_id, score) VALUES (?, ?, NULL)");
            foreach ($students as $s) {
                $gradeStmt->execute([$s['subject_enrollment_id'], $component_id]);
            }
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Activity created for all selected sections!']);

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>