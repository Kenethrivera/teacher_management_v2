<?php
// teacher/api/setup_class.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // 1. Get Teacher ID
    $stmt = $pdo->prepare("SELECT teacher_id FROM teachers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $teacher_id = $stmt->fetchColumn();
    if (!$teacher_id)
        throw new Exception("Teacher profile not found.");

    // 2. Validate Inputs
    if (empty($data['school_year_id']) || empty($data['subject_name'])) {
        throw new Exception("Subject Name and School Year are required.");
    }

    $pdo->beginTransaction();

    // --- A. HANDLE SUBJECT (Find or Create) ---
    $subName = trim($data['subject_name']);
    $subCode = trim($data['subject_code'] ?? '');

    // Auto-generate code if empty
    if (empty($subCode))
        $subCode = strtoupper(substr($subName, 0, 4));

    // Check by Name OR Code to avoid duplicates
    $stmt = $pdo->prepare("SELECT subject_id FROM subjects WHERE (subject_name = ? OR subject_code = ?) AND teacher_id = ?");
    $stmt->execute([$subName, $subCode, $teacher_id]);
    $subject_id = $stmt->fetchColumn();

    if (!$subject_id) {
        $stmt = $pdo->prepare("INSERT INTO subjects (subject_code, subject_name, teacher_id) VALUES (?, ?, ?)");
        $stmt->execute([$subCode, $subName, $teacher_id]);
        $subject_id = $pdo->lastInsertId();
    }

    // --- B. COLLECT SECTIONS ---
    $sections_to_link = [];

    // Existing Checkboxes
    if (!empty($data['existing_section_ids']) && is_array($data['existing_section_ids'])) {
        $sections_to_link = $data['existing_section_ids'];
    }

    // New Section Input
    if (!empty($data['new_section_name'])) {
        $newGrade = trim($data['new_section_grade'] ?? '0');
        $newName = trim($data['new_section_name']);

        $chk = $pdo->prepare("SELECT section_id FROM sections WHERE grade_level = ? AND section_name = ?");
        $chk->execute([$newGrade, $newName]);
        $new_sec_id = $chk->fetchColumn();

        if (!$new_sec_id) {
            $ins = $pdo->prepare("INSERT INTO sections (grade_level, section_name) VALUES (?, ?)");
            $ins->execute([$newGrade, $newName]);
            $new_sec_id = $pdo->lastInsertId();
        }
        $sections_to_link[] = $new_sec_id;
    }

    if (empty($sections_to_link)) {
        throw new Exception("Please select at least one section.");
    }

    // --- C. CREATE LINKS (ASSIGNMENTS & ENROLLMENTS) ---
    $linked_count = 0;

    // Statements
    $checkAssign = $pdo->prepare("SELECT assignment_id FROM subject_assignments WHERE teacher_id=? AND subject_id=? AND section_id=? AND school_year_id=?");
    $createAssign = $pdo->prepare("INSERT INTO subject_assignments (teacher_id, subject_id, section_id, school_year_id, is_active) VALUES (?, ?, ?, ?, 1)");

    // Statement to Backfill Students
    $backfillStudents = $pdo->prepare("
        INSERT INTO subject_enrollments (enrollment_id, subject_id, is_enrolled)
        SELECT e.id, ?, 1
        FROM enrollments e
        WHERE e.section_id = ? AND e.school_year_id = ? AND e.status = 'active'
        AND NOT EXISTS (
            SELECT 1 FROM subject_enrollments se WHERE se.enrollment_id = e.id AND se.subject_id = ?
        )
    ");

    foreach ($sections_to_link as $sec_id) {
        // 1. Assign Teacher to Section
        $checkAssign->execute([$teacher_id, $subject_id, $sec_id, $data['school_year_id']]);
        if (!$checkAssign->fetch()) {
            $createAssign->execute([$teacher_id, $subject_id, $sec_id, $data['school_year_id']]);
            $linked_count++;
        }

        // 2. Enroll Existing Students (The Fix)
        // Params: subject_id, section_id, school_year_id, subject_id (for NOT EXISTS check)
        $backfillStudents->execute([$subject_id, $sec_id, $data['school_year_id'], $subject_id]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => "Class saved! Linked to $linked_count section(s) and students updated."]);

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>