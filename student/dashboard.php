<?php
session_start();
require_once '../config/database.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 1. Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Fetch Student Profile (ADDED 's.lrn' to the selection)
$stmt = $pdo->prepare("
    SELECT s.student_id, s.lrn, s.first_name, s.last_name, 
           sec.section_name, sec.grade_level, sec.section_id
    FROM students s
    JOIN sections sec ON s.section_id = sec.section_id
    WHERE s.user_id = ?
");
$stmt->execute([$user_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student profile not found. Please contact admin.");
}

$student_id = $student['student_id'];
$_SESSION['name'] = $student['first_name'];

// 3. Fetch Statistics
// A. Count Subjects
$subStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM enrollments e
    JOIN subject_enrollments se ON e.id = se.enrollment_id
    WHERE e.student_id = ? AND e.status = 'active' AND se.is_enrolled = 1
");
$subStmt->execute([$student_id]);
$totalSubjects = $subStmt->fetchColumn();

// B. Count Pending Activities
$pendStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM activities a
    JOIN subject_assignments sa ON a.assignment_id = sa.assignment_id
    JOIN enrollments e ON e.section_id = sa.section_id
    JOIN subject_enrollments se ON se.enrollment_id = e.id AND se.subject_id = sa.subject_id
    WHERE e.student_id = ? 
      AND a.is_published = 1
      AND NOT EXISTS (
          SELECT 1 FROM activity_submissions sub 
          WHERE sub.activity_id = a.activity_id AND sub.student_id = e.student_id
      )
");
$pendStmt->execute([$student_id]);
$pendingCount = $pendStmt->fetchColumn();

// 4. Fetch Upcoming Deadlines
$deadlineStmt = $pdo->prepare("
    SELECT a.title, a.due_date, s.subject_name, a.activity_type
    FROM activities a
    JOIN subject_assignments sa ON a.assignment_id = sa.assignment_id
    JOIN subjects s ON sa.subject_id = s.subject_id
    JOIN enrollments e ON e.section_id = sa.section_id
    JOIN subject_enrollments se ON se.enrollment_id = e.id AND se.subject_id = sa.subject_id
    WHERE e.student_id = ? 
      AND a.is_published = 1
      AND a.due_date >= NOW()
      AND NOT EXISTS (
          SELECT 1 FROM activity_submissions sub 
          WHERE sub.activity_id = a.activity_id AND sub.student_id = e.student_id
      )
    ORDER BY a.due_date ASC
    LIMIT 5
");
$deadlineStmt->execute([$student_id]);
$deadlines = $deadlineStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .stat-card {
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .icon-box {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.5rem;
        }
    </style>
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row mb-4 align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold text-dark">Hello, <?= htmlspecialchars($student['first_name']) ?>! 👋</h2>

                <p class="text-muted">
                    Grade <?= htmlspecialchars($student['grade_level']) ?> -
                    <?= htmlspecialchars($student['section_name']) ?>
                    | LRN: <?= htmlspecialchars($student['lrn']) ?>
                </p>

            </div>
            <div class="col-md-4 text-md-end">
                <span class="badge bg-primary fs-6 px-3 py-2">
                    <?= date('F j, Y') ?>
                </span>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-book"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Enrolled Subjects</h6>
                            <h3 class="fw-bold mb-0"><?= $totalSubjects ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning me-3">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Pending Tasks</h6>
                            <h3 class="fw-bold mb-0"><?= $pendingCount ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">My Section</h6>
                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($student['section_name']) ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-calendar-event me-2"></i>Upcoming Deadlines</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (empty($deadlines)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-check-circle display-4 d-block mb-2"></i>
                                    No upcoming deadlines! You're all caught up.
                                </div>
                            <?php else: ?>
                                <?php foreach ($deadlines as $d): ?>
                                    <a href="activities.php"
                                        class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($d['title']) ?></div>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($d['subject_name']) ?> •
                                                <span class="text-uppercase"><?= htmlspecialchars($d['activity_type']) ?></span>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-danger">Due:
                                                <?= date('M j, g:i a', strtotime($d['due_date'])) ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <a href="activities.php" class="text-decoration-none small fw-bold">View All Activities
                            &rarr;</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Quick Actions</h5>
                        <div class="d-grid gap-2">
                            <a href="activities.php" class="btn btn-outline-primary text-start">
                                <i class="bi bi-pencil-square me-2"></i> Take a Quiz
                            </a>
                            <a href="activities.php" class="btn btn-outline-primary text-start">
                                <i class="bi bi-upload me-2"></i> Upload Assignment
                            </a>
                            <a href="grades.php" class="btn btn-outline-success text-start">
                                <i class="bi bi-graph-up me-2"></i> View Grades
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>