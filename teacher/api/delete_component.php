<?php
// teacher/api/delete_component.php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['user_id']) || !isset($data['component_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // 1. Get Component Details
    $stmt = $pdo->prepare("SELECT * FROM grading_components WHERE component_id = ?");
    $stmt->execute([$data['component_id']]);
    $comp = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comp) {
        throw new Exception("Column not found.");
    }

    // 2. Check if linked to an Activity
    // We match the unique keys (Assignment + Quarter + Type + Item #)
    $check = $pdo->prepare("
        SELECT activity_id FROM activities 
        WHERE assignment_id = ? 
        AND quarter = ? 
        AND component_type = ? 
        AND item_number = ?
    ");
    $check->execute([
        $comp['assignment_id'],
        $comp['quarter'],
        $comp['component_type'],
        $comp['item_number']
    ]);

    if ($check->fetch()) {
        throw new Exception("Cannot delete: This column is linked to an Activity (Quiz/File). Please delete the Activity instead.");
    }

    // 3. Safe to Delete (Manually created column)
    $del = $pdo->prepare("DELETE FROM grading_components WHERE component_id = ?");
    $del->execute([$data['component_id']]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>