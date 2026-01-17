<?php
require_once '../../middleware/auth.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

$activity_id = $_GET['activity_id'] ?? null;

if (!$activity_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Activity ID required']);
    exit;
}

try {
    // 1. Get Activity Info & Section Details
    $stmt = $pdo->prepare("
        SELECT a.max_score, sa.section_id, sa.school_year_id, sa.subject_id
        FROM activities a
        JOIN subject_assignments sa ON a.assignment_id = sa.assignment_id
        WHERE a.activity_id = ?
    ");
    $stmt->execute([$activity_id]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$activity) {
        throw new Exception("Activity not found");
    }

    // 2. Get All Students in Section + Their Submission (Left Join)
    $sql = "
        SELECT 
            s.student_id, s.last_name, s.first_name,
            sub.submission_id, sub.submitted_at, sub.status, sub.score
        FROM students s
        JOIN enrollments e ON s.student_id = e.student_id
        -- Link to submissions for THIS activity
        LEFT JOIN activity_submissions sub ON s.student_id = sub.student_id AND sub.activity_id = ?
        WHERE e.section_id = ? 
        AND e.school_year_id = ?
        AND e.status = 'active'
        ORDER BY s.last_name, s.first_name
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$activity_id, $activity['section_id'], $activity['school_year_id']]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'max_score' => $activity['max_score'],
        'students' => $results
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>