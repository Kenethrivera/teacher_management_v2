<?php
// teacher/api/add_component.php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['user_id']) || !isset($data['assignment_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or missing data']);
    exit;
}

try {
    // 1. Find the highest item number for this specific group
    $stmt = $pdo->prepare("
        SELECT MAX(item_number) 
        FROM grading_components 
        WHERE assignment_id = ? AND quarter = ? AND component_type = ?
    ");
    $stmt->execute([$data['assignment_id'], $data['quarter'], $data['component_type']]);
    $maxItem = $stmt->fetchColumn();

    $newItem = ($maxItem) ? $maxItem + 1 : 1;

    // 2. Insert new component
    $insert = $pdo->prepare("
        INSERT INTO grading_components (assignment_id, quarter, component_type, item_number, max_score, date_given)
        VALUES (?, ?, ?, ?, 10, CURDATE())
    ");
    $insert->execute([$data['assignment_id'], $data['quarter'], $data['component_type'], $newItem]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>