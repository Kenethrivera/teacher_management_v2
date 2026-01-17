<?php
// student/profile.php
session_start();
require_once '../config/database.php';

// 1. Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

// 2. AUTO-DETECT STUDENT ID (The Fix)
if (!isset($_SESSION['student_id'])) {
    $stmt = $pdo->prepare("SELECT student_id FROM students WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $sid = $stmt->fetchColumn();
    if ($sid) {
        $_SESSION['student_id'] = $sid;
    } else {
        die("Error: Student profile not linked to this user.");
    }
}

// 3. Fetch Student Data
$stmt = $pdo->prepare("
    SELECT s.first_name, s.last_name, s.lrn, s.sex, sec.section_name, sec.grade_level, u.email 
    FROM students s
    JOIN users u ON s.user_id = u.user_id
    JOIN sections sec ON s.section_id = sec.section_id
    WHERE s.student_id = ?
");
$stmt->execute([$_SESSION['student_id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile)
    die("Profile not found.");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .profile-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">

            <div class="col-md-4 mb-4">
                <div class="card profile-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto"
                                style="width: 80px; height: 80px; font-size: 2rem;">
                                <?php echo substr($profile['first_name'], 0, 1); ?>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-1"><?php echo $profile['first_name'] . ' ' . $profile['last_name']; ?>
                        </h5>
                        <p class="text-muted small mb-3">LRN: <?php echo $profile['lrn']; ?></p>

                        <div class="border-top pt-3 text-start">
                            <div class="mb-2"><small class="text-muted fw-bold">Grade &
                                    Section</small><br><?php echo $profile['grade_level'] . ' - ' . $profile['section_name']; ?>
                            </div>
                            <div class="mb-2"><small
                                    class="text-muted fw-bold">Gender</small><br><?php echo $profile['sex']; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8 mb-4">
                <div class="card profile-card">
                    <div class="card-header bg-white py-3 fw-bold">
                        <i class="bi bi-shield-lock me-2"></i> Account Security
                    </div>
                    <div class="card-body p-4">
                        <div id="alertBox"></div>

                        <form id="profileForm">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Email Address (Login)</label>
                                <input type="email" name="email" class="form-control"
                                    value="<?php echo $profile['email']; ?>" required>
                                <div class="form-text">If you change this, you must use the new email to log in.</div>
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold mb-3 text-primary">Change Password</h6>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">New Password</label>
                                    <input type="password" name="new_password" class="form-control"
                                        placeholder="Leave blank to keep current">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Confirm New Password</label>
                                    <input type="password" id="confirm_password" class="form-control"
                                        placeholder="Confirm new password">
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-3 bg-light p-3 rounded border">
                                <label class="form-label small fw-bold text-danger">Current Password (Required to
                                    Save)</label>
                                <input type="password" name="current_password" class="form-control" required
                                    placeholder="Enter your current password to confirm changes">
                            </div>

                            <button type="submit" class="btn btn-primary" id="saveBtn">
                                <i class="bi bi-check-circle me-2"></i> Save Changes
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('profileForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const newPass = document.querySelector('input[name="new_password"]').value;
            const confirmPass = document.getElementById('confirm_password').value;
            const btn = document.getElementById('saveBtn');
            const alertBox = document.getElementById('alertBox');

            if (newPass && newPass !== confirmPass) {
                alertBox.innerHTML = '<div class="alert alert-danger">New passwords do not match.</div>';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = 'Saving...';

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            fetch('actions/update_profile.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        alertBox.innerHTML = `<div class="alert alert-success">${res.message}</div>`;
                        document.querySelector('input[name="new_password"]').value = '';
                        document.getElementById('confirm_password').value = '';
                        document.querySelector('input[name="current_password"]').value = '';
                    } else {
                        alertBox.innerHTML = `<div class="alert alert-danger">${res.message}</div>`;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alertBox.innerHTML = '<div class="alert alert-danger">System Error. Check console.</div>';
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-circle me-2"></i> Save Changes';
                });
        });
    </script>
</body>

</html>