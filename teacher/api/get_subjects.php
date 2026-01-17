<?php
require_once '../../middleware/auth.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

try {
    // Get teacher_id from user_id
    $stmt = $pdo->prepare("SELECT teacher_id FROM teachers WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$teacher) {
        echo json_encode([]);
        exit;
    }

    $teacher_id = $teacher['teacher_id'];

    // Get distinct subjects this teacher is assigned to
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.subject_id, s.subject_name, s.subject_code
        FROM subjects s
        INNER JOIN subject_assignments sa ON s.subject_id = sa.subject_id
        WHERE sa.teacher_id = ?
        AND sa.is_active = 1
        ORDER BY s.subject_name
    ");

    $stmt->execute([$teacher_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>