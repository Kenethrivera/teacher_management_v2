<?php
header('Content-Type: application/json');
require '../../config/database.php';

// Collect POST data
$lrn = $_POST['lrn'] ?? '';
$full_name = $_POST['full_name'] ?? '';
$sex = $_POST['sex'] ?? '';
$age = $_POST['age'] ?? '';
$section_id = $_POST['section_id'] ?? 0;

if (!$lrn || !$full_name || !$sex || !$age || !$section_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

// Split full name
if (strpos($full_name, ',') === false) {
    echo json_encode(['success' => false, 'message' => 'Full name must be in format Last, First']);
    exit;
}
list($last_name, $first_name) = array_map('trim', explode(',', $full_name));

// Create a unique username (first.last)
$username = strtolower($first_name . '.' . $last_name);

// Check if username already exists
$stmtCheck = $pdo->prepare("SELECT user_id FROM users WHERE username=? LIMIT 1");
$stmtCheck->execute([$username]);
if ($stmtCheck->rowCount() > 0) {
    $username .= rand(10, 99); // add a number if duplicate
}

// Default password
$hashedPassword = password_hash($lrn, PASSWORD_DEFAULT);


try {
    $pdo->beginTransaction();

    // Insert into users table
    $stmtUser = $pdo->prepare("INSERT INTO users (username, email, password, role, is_active) VALUES (?,?,?,?,1)");
    $stmtUser->execute([$username, $username . '@school.com', $hashedPassword, 'student']);

    $user_id = $pdo->lastInsertId();

    // Insert into students table
    $stmtStudent = $pdo->prepare("INSERT INTO students (user_id, lrn, first_name, last_name, sex, age, status, section_id) VALUES (?,?,?,?,?,?, 'Enrolled', ?)");
    $stmtStudent->execute([$user_id, $lrn, $first_name, $last_name, $sex, $age, $section_id]);

    $pdo->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
