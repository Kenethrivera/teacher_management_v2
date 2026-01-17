<?php
// teacher/api/get_all_sections.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

// Check auth
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

try {
    // Fetch all sections for the checklist
    $stmt = $pdo->query("SELECT section_id, grade_level, section_name FROM sections ORDER BY grade_level, section_name");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo json_encode([]);
}
?>