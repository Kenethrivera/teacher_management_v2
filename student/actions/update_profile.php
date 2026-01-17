<?php
// student/actions/update_profile.php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

// 1. Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$current_password = $data['current_password'] ?? '';
$new_email = trim($data['email'] ?? '');
$new_password = $data['new_password'] ?? '';

// 2. Validate Inputs
if (empty($current_password)) {
    echo json_encode(['success' => false, 'message' => 'Current password is required to save changes.']);
    exit;
}

try {
    // 3. Fetch User Data (Specifically the Password Hash)
    $stmt = $pdo->prepare("SELECT password, email FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    // 4. VERIFY CURRENT PASSWORD (The Critical Security Step)
    // We use password_verify() to compare the typed text vs the database hash
    if (!password_verify($current_password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
        exit;
    }

    $updates = [];
    $params = [];

    // 5. Handle Email Update
    if (!empty($new_email) && $new_email !== $user['email']) {
        // Check uniqueness
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $check->execute([$new_email, $user_id]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email is already in use.']);
            exit;
        }
        $updates[] = "email = ?";
        $params[] = $new_email;
    }

    // 6. Handle New Password (HASH IT!)
    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters.']);
            exit;
        }
        $updates[] = "password = ?";
        // ENCRYPT the new password before saving
        $params[] = password_hash($new_password, PASSWORD_DEFAULT);
    }

    // 7. Execute Updates
    if (empty($updates)) {
        echo json_encode(['success' => true, 'message' => 'No changes made.']);
        exit;
    }

    $params[] = $user_id; // For WHERE clause
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE user_id = ?";
    $pdo->prepare($sql)->execute($params);

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>