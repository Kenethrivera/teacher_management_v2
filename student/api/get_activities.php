<?php
session_start();
require_once '../../config/database.php'; // Adjust path if needed

header('Content-Type: application/json');

// Check auth
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get Student ID
$stmt = $pdo->prepare("SELECT student_id FROM students WHERE user_id = ?");
$stmt->execute([$user_id]);
$student_id = $stmt->fetchColumn();

if (!$student_id) {
    echo json_encode([]);
    exit;
}

// Query activities
// We need: title, subject_name, status, score, max_score, due_date, description, activity_type, activity_id
// Status logic:
// 1. Join activity_submissions on student_id AND activity_id
// 2. If submission exists, use its status.
// 3. If not, status is 'pending'.
$sql = "
    SELECT 
        a.activity_id, a.title, a.description, a.activity_type, a.max_score, a.due_date,
        s.subject_name,
        COALESCE(sub.status, 'pending') as status,
        sub.score,
        sub.submitted_at
    FROM activities a
    JOIN subject_assignments sa ON a.assignment_id = sa.assignment_id
    JOIN subjects s ON sa.subject_id = s.subject_id
    JOIN enrollments e ON sa.section_id = e.section_id
    JOIN subject_enrollments se ON e.id = se.enrollment_id AND se.subject_id = s.subject_id
    LEFT JOIN activity_submissions sub 
        ON a.activity_id = sub.activity_id 
        AND sub.student_id = e.student_id
    WHERE e.student_id = ?
    AND a.is_published = 1
    ORDER BY a.due_date ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$student_id]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
?>