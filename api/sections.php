<?php
require_once '../config/database.php';

$stmt = $pdo->query("SELECT section_id, grade_level, section_name FROM sections ORDER BY grade_level, section_name");
$sections = $stmt->fetchAll();

echo json_encode($sections);
