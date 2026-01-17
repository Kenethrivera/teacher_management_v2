<?php
require_once '../../config/database.php';

$grade = $_POST['grade_level'];
$section = $_POST['section_name'];

$stmt = $pdo->prepare("INSERT INTO sections (grade_level, section_name) VALUES (?, ?)");
$stmt->execute([$grade, $section]);

echo json_encode(['success' => true, 'section_id' => $pdo->lastInsertId()]);
