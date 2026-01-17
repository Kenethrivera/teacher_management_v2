<?php
// teacher/api/manage_student.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['user_id']))
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));

try {
    $pdo->beginTransaction();
    $action = $data['action'];

    if ($action === 'add') {
        // 1. Create User
        $email = trim($data['email']);
        $stmt = $pdo->prepare("INSERT INTO users (email, username, password, role, is_active) VALUES (?, ?, ?, 'student', 1)");
        $stmt->execute([$email, $data['lrn'], password_hash($data['password'], PASSWORD_DEFAULT)]);
        $user_id = $pdo->lastInsertId();

        // 2. Create Student Profile
        $stmt = $pdo->prepare("INSERT INTO students (user_id, lrn, first_name, last_name, sex, age, section_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Enrolled')");
        $stmt->execute([$user_id, $data['lrn'], $data['first_name'], $data['last_name'], $data['sex'], $data['age'], $data['section_id']]);
        $student_id = $pdo->lastInsertId();

        // 3. Enroll in Section & SY
        $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, section_id, school_year_id, status) VALUES (?, ?, ?, 'active')");
        $stmt->execute([$student_id, $data['section_id'], $data['school_year_id']]);
        $enrollment_id = $pdo->lastInsertId();

        // 4. Enroll in Subject
        $stmt = $pdo->prepare("INSERT INTO subject_enrollments (enrollment_id, subject_id, is_enrolled) VALUES (?, ?, 1)");
        $stmt->execute([$enrollment_id, $data['subject_id']]);
        $sub_enroll_id = $pdo->lastInsertId();

        // 5. Initialize Quarter (Optional)
        if (!empty($data['quarter'])) {
            $stmt = $pdo->prepare("INSERT INTO computed_grades (subject_enrollment_id, quarter, final_grade, is_released) VALUES (?, ?, 0.00, 0)");
            $stmt->execute([$sub_enroll_id, $data['quarter']]);
        }

        echo json_encode(['success' => true, 'message' => 'Student added and enrolled!']);

    } elseif ($action === 'edit') {
        $stmt = $pdo->prepare("UPDATE students SET lrn=?, first_name=?, last_name=?, sex=?, age=?, status=? WHERE student_id=?");
        $stmt->execute([$data['lrn'], $data['first_name'], $data['last_name'], $data['sex'], $data['age'], $data['status'], $data['student_id']]);
        echo json_encode(['success' => true, 'message' => 'Student info updated!']);
    }

    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>