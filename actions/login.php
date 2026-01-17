<?php
// actions/login.php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

if ($email === '' || $password === '' || !in_array($role, ['teacher', 'student'])) {
    $_SESSION['error'] = "Invalid login request.";
    header("Location: ../login.php");
    exit;
}

/* 1️⃣ Fetch user */
$stmt = $pdo->prepare("
    SELECT user_id, email, username, password, role, is_active
    FROM users
    WHERE email = ?
    LIMIT 1
");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user exists
if (!$user) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: ../login.php");
    exit;
}

/* 2️⃣ Active check */
if ((int) $user['is_active'] !== 1) {
    $_SESSION['error'] = "Account inactive.";
    header("Location: ../login.php");
    exit;
}

/* 3️⃣ Role check */
// Prevents a student from logging in on the Teacher tab and vice versa
if ($user['role'] !== $role) {
    $_SESSION['error'] = "Account exists but role mismatch. Please switch tabs.";
    header("Location: ../login.php");
    exit;
}

/* 4️⃣ SECURE PASSWORD CHECK (Updated) */
// password_verify() checks the plain text input against the database hash
if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: ../login.php");
    exit;
}

/* 5️⃣ Student enrollment check */
if ($role === 'student') {
    $stmt = $pdo->prepare("
        SELECT status FROM students 
        WHERE user_id = ? 
        LIMIT 1
    ");
    $stmt->execute([$user['user_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student || $student['status'] !== 'Enrolled') {
        $_SESSION['error'] = "You are not currently enrolled.";
        header("Location: ../login.php");
        exit;
    }
}

/* 6️⃣ Get display name */
if ($role === 'teacher') {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM teachers WHERE user_id = ?");
} else {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM students WHERE user_id = ?");
}

$stmt->execute([$user['user_id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Set Session Variables
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['role'] = $user['role'];
$_SESSION['name'] = $profile
    ? $profile['first_name'] . ' ' . $profile['last_name']
    : $user['username'];
$_SESSION['logged_in'] = true;

/* 7️⃣ Redirect */
if ($role === 'teacher') {
    header("Location: ../teacher/dashboard.php");
} else {
    header("Location: ../student/dashboard.php");
}
exit;
?>