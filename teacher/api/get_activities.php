<?php
require_once '../../middleware/auth.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

// 1. Get Teacher ID
$stmt = $pdo->prepare("SELECT teacher_id FROM teachers WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    echo json_encode([]);
    exit;
}
$teacher_id = $teacher['teacher_id'];

// 2. Filters
$school_year = $_GET['school_year_id'] ?? null;
$subject = $_GET['subject_id'] ?? null;
$quarter = $_GET['quarter'] ?? null;
$type = $_GET['activity_type'] ?? null;
$status = $_GET['is_published'] ?? null;

// 3. Query
$sql = "
    SELECT 
        a.activity_id, a.title, a.description, a.activity_type, 
        a.component_type, a.item_number, a.max_score, a.due_date, 
        a.is_published, a.quarter,
        s.subject_name, sec.section_name, sec.grade_level,
        sy.school_year
    FROM activities a
    JOIN subject_assignments sa ON a.assignment_id = sa.assignment_id
    JOIN subjects s ON sa.subject_id = s.subject_id
    JOIN sections sec ON sa.section_id = sec.section_id
    JOIN school_years sy ON sa.school_year_id = sy.id
    WHERE sa.teacher_id = ?
";

$params = [$teacher_id];

if ($school_year) {
    $sql .= " AND sa.school_year_id = ?";
    $params[] = $school_year;
}
if ($subject) {
    $sql .= " AND sa.subject_id = ?";
    $params[] = $subject;
}
if ($quarter) {
    $sql .= " AND a.quarter = ?";
    $params[] = $quarter;
}
if ($type) {
    $sql .= " AND a.activity_type = ?";
    $params[] = $type;
}
if ($status !== null && $status !== '') {
    $sql .= " AND a.is_published = ?";
    $params[] = $status;
}

$sql .= " ORDER BY a.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>