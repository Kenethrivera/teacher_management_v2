<?php
session_start();

require_once 'config/database.php';

// 1. Prevent Caching (Forces browser to reload page)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 2. Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'teacher') {
        header("Location: teacher/dashboard.php");
    } else {
        header("Location: student/dashboard.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Classroom Manager | Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .role-btn.active {
            border-color: #0d6efd;
            box-shadow: 0 0 0 .15rem rgba(13, 110, 253, .25);
            background-color: #e7f1ff;
            color: #0d6efd;
        }
    </style>
</head>

<body>

    <div class="min-vh-100 d-flex align-items-center justify-content-center">
        <div class="w-100" style="max-width: 380px;">

            <!-- Header -->
            <div class="text-center mb-2">
                <div class="mx-auto mb-1 d-flex align-items-center justify-content-center rounded bg-primary text-white"
                    style="width: 40px; height: 40px;">
                    <i class="bi bi-book fs-5"></i>
                </div>
                <h5 class="fw-bold mb-0">Classroom Manager</h5>
                <small class="text-muted">Sign in to access your dashboard</small>
            </div>

            <!-- Card -->
            <div class="card shadow-sm">
                <div class="card-body p-3">

                    <!-- Role Selection -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1">Select Role</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <button type="button" class="btn btn-sm w-100 role-btn active" id="teacherBtn">
                                    <i class="bi bi-person me-1"></i> Teacher
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-sm w-100 role-btn" id="studentBtn">
                                    <i class="bi bi-mortarboard me-1"></i> Student
                                </button>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($_SESSION['error'])): ?>
                        <div class="alert alert-danger small">
                            <?= $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form method="POST" action="actions/login.php" id="loginForm">
                        <input type="hidden" name="role" id="roleInput" value="teacher">

                        <div class="mb-2">
                            <label class="form-label small mb-1">Email</label>
                            <input type="email" class="form-control form-control-sm" name="email" id="emailInput"
                                placeholder="teacher@school.edu" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small mb-1">Password</label>
                            <input type="password" class="form-control form-control-sm" name="password" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100" id="loginBtn">
                            Sign in as Teacher
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="my-2 text-center small text-muted">
                        <hr class="my-2">
                        REMINDER
                    </div>

                    <div class="text-center text-muted small">
                        <div>Please go see your professor for account</div>
                        <div>creation and approval</div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const teacherBtn = document.getElementById('teacherBtn');
        const studentBtn = document.getElementById('studentBtn');
        const roleInput = document.getElementById('roleInput');
        const emailInput = document.getElementById('emailInput');
        const loginBtn = document.getElementById('loginBtn');

        function setRole(role) {
            roleInput.value = role;
            teacherBtn.classList.toggle('active', role === 'teacher');
            studentBtn.classList.toggle('active', role === 'student');
            emailInput.placeholder = role === 'teacher'
                ? 'teacher@school.edu'
                : 'student@school.edu';
            loginBtn.textContent = role === 'teacher'
                ? 'Sign in as Teacher'
                : 'Sign in as Student';
        }

        teacherBtn.onclick = () => setRole('teacher');
        studentBtn.onclick = () => setRole('student');

        document.getElementById('loginForm').addEventListener('submit', () => {
            loginBtn.disabled = true;
            loginBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-1"></span>
            Signing in...
        `;
        });
    </script>

</body>

</html>