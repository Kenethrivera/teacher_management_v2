<?php
// student/api/get_grade_details.php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']))
    exit(json_encode([]));

$student_id = $_SESSION['student_id'];
$subject_name = $_GET['subject'] ?? '';
$quarter = $_GET['quarter'] ?? 1;

try {
    // 1. Get components and scores for this student, subject, and quarter
    $sql = "
        SELECT 
            gc.description, 
            gc.component_type, 
            gc.max_score,
            COALESCE(g.score, 0) as score
        FROM students s
        JOIN enrollments e ON s.student_id = e.student_id
        JOIN subject_enrollments se ON e.id = se.enrollment_id
        JOIN subjects sub ON se.subject_id = sub.subject_id
        JOIN subject_assignments sa ON sa.subject_id = sub.subject_id AND sa.section_id = e.section_id
        JOIN grading_components gc ON gc.assignment_id = sa.assignment_id
        LEFT JOIN grades g ON g.component_id = gc.component_id AND g.subject_enrollment_id = se.id
        WHERE s.student_id = ? 
        AND sub.subject_name = ?
        AND gc.quarter = ?
        ORDER BY gc.component_type, gc.item_number
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id, $subject_name, $quarter]);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($details);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>