<?php
require_once '../config/database.php';

// Fetch students with optional filters
$section = $_GET['section'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT s.student_id, s.lrn, s.first_name, s.last_name, s.sex, s.age, s.status, sec.grade_level, sec.section_name
        FROM students s
        JOIN sections sec ON s.section_id = sec.section_id
        WHERE 1 ";

$params = [];

// Apply filters
if ($section && $section !== 'All') {
    $sql .= " AND s.section_id = ?";
    $params[] = $section;
}

if ($status && $status !== 'All') {
    $sql .= " AND s.status = ?";
    $params[] = $status;
}

if ($search) {
    $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

echo json_encode($students);
