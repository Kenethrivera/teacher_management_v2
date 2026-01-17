<?php
session_start();
require_once '../config/database.php';

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$role = $_POST['role'] ?? '';

// Basic validation
if ($email === '' || $password === '' || !in_array($role, ['teacher', 'student'])) {
    $_SESSION['error'] = "Invalid login request.";
    header("Location: ../login.php");
    exit;
}

// 1️⃣ Fetch user
$sql = "SELECT * FROM users 
        WHERE username = ? 
        AND role = ? 
        AND is_active = 1 
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email, $role]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: ../login.php");
    exit;
}

// 2️⃣ Verify password
if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: ../login.php");
    exit;
}

// 3️⃣ Student enrollment check
if ($role === 'student') {
    $stmt = $pdo->prepare("SELECT status FROM students WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user['user_id']]);
    $student = $stmt->fetch();

    if (!$student || $student['status'] !== 'Enrolled') {
        $_SESSION['error'] = "You are not currently enrolled.";
        header("Location: ../login.php");
        exit;
    }
}

// 4️⃣ Get display name
if ($role === 'teacher') {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM teachers WHERE user_id = ?");
} else {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM students WHERE user_id = ?");
}
$stmt->execute([$user['user_id']]);
$profile = $stmt->fetch();

// 5️⃣ Set session
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['role'] = $role;
$_SESSION['name'] = $profile['first_name'] . ' ' . $profile['last_name'];
$_SESSION['logged_in'] = true;

// 6️⃣ Redirect by role
if ($role === 'teacher') {
    header("Location: ../teacher/dashboard.php");
} else {
    header("Location: ../student/dashboard.php");
}
exit;
