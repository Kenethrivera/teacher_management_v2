<?php
// teacher/api/get_masterlist.php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']))
    exit(json_encode([]));

$teacher_id = $_SESSION['teacher_id'] ?? 0;
$sy_id = $_GET['school_year_id'] ?? 0;
$subject_id = $_GET['subject_id'] ?? 0;
$section_id = $_GET['section_id'] ?? 0;

try {
    // Fetch Students with Enrollment Data
    $sql = "
        SELECT 
            s.student_id,
            s.lrn,
            s.last_name,
            s.first_name,
            s.sex,
            s.age,
            s.status,
            u.email,
            e.id as enrollment_id
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        JOIN enrollments e ON s.student_id = e.student_id
        JOIN subject_enrollments se ON e.id = se.enrollment_id
        WHERE e.section_id = ? 
        AND se.subject_id = ? 
        AND e.school_year_id = ?
        ORDER BY s.sex DESC, s.last_name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$section_id, $subject_id, $sy_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($students);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>