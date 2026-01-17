<?php
require_once '../../config/database.php';

$id = $_POST['id'];
$lrn = $_POST['lrn'];
$full_name = $_POST['full_name'];
$sex = $_POST['sex'];
$age = $_POST['age'];
$status = $_POST['status'];
$section_id = $_POST['section_id'];

list($last_name, $first_name) = array_map('trim', explode(',', $full_name));

$stmt = $pdo->prepare("UPDATE students SET lrn=?, first_name=?, last_name=?, sex=?, age=?, status=?, section_id=? WHERE student_id=?");
$stmt->execute([$lrn, $first_name, $last_name, $sex, $age, $status, $section_id, $id]);

echo json_encode(['success' => true]);
