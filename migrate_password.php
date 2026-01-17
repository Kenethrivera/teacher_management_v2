<?php
// run this in the browser once to migrate plain text passwords to hashed passwords
// migrate_passwords.php
require_once 'config/database.php';

echo "<h1>Password Migration Tool</h1>";

// 1. Get all users
$stmt = $pdo->query("SELECT user_id, password FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = 0;

foreach ($users as $user) {
    $current_pass = $user['password'];

    // Check if it's already a hash (Bcrypt hashes are 60 chars long)
    if (strlen($current_pass) < 60) {
        // It's plain text, hash it!
        $new_hash = password_hash($current_pass, PASSWORD_DEFAULT);

        $update = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update->execute([$new_hash, $user['user_id']]);

        echo "Updated User ID {$user['user_id']}: Hashed successfully.<br>";
        $count++;
    } else {
        echo "User ID {$user['user_id']}: Already hashed. Skipped.<br>";
    }
}

echo "<h3>Migration Complete. Updated $count passwords.</h3>";
echo "<p style='color:red'>PLEASE DELETE THIS FILE NOW.</p>";
?>


