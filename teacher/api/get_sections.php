<?php
require_once '../../middleware/auth.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$subject_id = $_GET['subject_id'] ?? null;
$school_year_id = $_GET['school_year_id'] ?? null;

try {
    // Get teacher_id
    $stmt = $pdo->prepare("SELECT teacher_id FROM teachers WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$teacher) {
        echo json_encode([]);
        exit;
    }

    $teacher_id = $teacher['teacher_id'];

    // Get sections where this teacher teaches this subject
    $sql = "
        SELECT DISTINCT sec.section_id, sec.grade_level, sec.section_name,
               sa.assignment_id
        FROM sections sec
        INNER JOIN subject_assignments sa ON sec.section_id = sa.section_id
        WHERE sa.teacher_id = ?
        AND sa.is_active = 1
    ";

    $params = [$teacher_id];

    if ($subject_id) {
        $sql .= " AND sa.subject_id = ?";
        $params[] = $subject_id;
    }

    if ($school_year_id) {
        $sql .= " AND sa.school_year_id = ?";
        $params[] = $school_year_id;
    }

    $sql .= " ORDER BY sec.grade_level, sec.section_name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>