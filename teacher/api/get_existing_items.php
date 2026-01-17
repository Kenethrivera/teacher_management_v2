<?php
require_once '../../middleware/auth.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

$assignment_id = $_GET['assignment_id'] ?? null;
$quarter = $_GET['quarter'] ?? null;
$component_type = $_GET['component_type'] ?? null;

if (!$assignment_id || !$quarter || !$component_type) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT item_number 
        FROM activities 
        WHERE assignment_id = ? 
        AND quarter = ? 
        AND component_type = ?
        ORDER BY item_number ASC
    ");

    $stmt->execute([$assignment_id, $quarter, $component_type]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($items);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>