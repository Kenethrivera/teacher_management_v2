<?php
// teacher/api/add_class.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

// 1. Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// 2. Validate Inputs
if (empty($data['school_year_id']) || empty($data['grade_level']) || empty($data['section_name']) || empty($data['subject_name'])) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields (SY, Grade, Section, Subject).']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 3. Get Teacher ID
    $stmt = $pdo->prepare("SELECT teacher_id FROM teachers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $teacher_id = $stmt->fetchColumn();
    if (!$teacher_id)
        throw new Exception("Teacher profile not found.");

    // 4. Handle SECTION (Find or Create)
    $grade = trim($data['grade_level']);
    $secName = trim($data['section_name']);

    // Check if exists
    $stmt = $pdo->prepare("SELECT section_id FROM sections WHERE grade_level = ? AND section_name = ?");
    $stmt->execute([$grade, $secName]);
    $section_id = $stmt->fetchColumn();

    if (!$section_id) {
        // Create new
        $stmt = $pdo->prepare("INSERT INTO sections (grade_level, section_name) VALUES (?, ?)");
        $stmt->execute([$grade, $secName]);
        $section_id = $pdo->lastInsertId();
    }

    // 5. Handle SUBJECT (Find or Create)
    $subCode = trim($data['subject_code'] ?? strtoupper(substr($data['subject_name'], 0, 3) . $grade)); // Auto-code if empty
    $subName = trim($data['subject_name']);

    $stmt = $pdo->prepare("SELECT subject_id FROM subjects WHERE subject_name = ? AND subject_code = ?");
    $stmt->execute([$subName, $subCode]);
    $subject_id = $stmt->fetchColumn();

    if (!$subject_id) {
        // Create new (Linked to this teacher initially)
        $stmt = $pdo->prepare("INSERT INTO subjects (subject_code, subject_name, teacher_id) VALUES (?, ?, ?)");
        $stmt->execute([$subCode, $subName, $teacher_id]);
        $subject_id = $pdo->lastInsertId();
    }

    // 6. Handle ASSIGNMENT (The Link)
    // Link: Teacher + Section + Subject + SY
    $sy_id = $data['school_year_id'];

    $check = $pdo->prepare("SELECT assignment_id FROM subject_assignments WHERE teacher_id=? AND section_id=? AND subject_id=? AND school_year_id=?");
    $check->execute([$teacher_id, $section_id, $subject_id, $sy_id]);

    if ($check->fetch()) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'You already have this class in your list.']);
        exit;
    }

    $link = $pdo->prepare("INSERT INTO subject_assignments (teacher_id, section_id, subject_id, school_year_id, is_active) VALUES (?, ?, ?, ?, 1)");
    $link->execute([$teacher_id, $section_id, $subject_id, $sy_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Class added successfully!']);

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>