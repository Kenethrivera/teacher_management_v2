<?php
// teacher/dashboard.php
session_start();

// 1. Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

// 2. Prevent Caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        .section {
            display: none;
        }

        .section.active {
            display: block;
        }
    </style>
</head>

<body class="bg-light">

    <?php include '../includes/navbar_teacher.php'; ?>

    <div class="container-fluid mt-4 px-4">
        <div id="dashboard" class="section">
            <?php include 'sections/dashboard.php'; ?>
        </div>
        <div id="masterlist" class="section">
            <?php include 'sections/masterlist.php'; ?>
        </div>
        <div id="classrecord" class="section">
            <?php include 'sections/class_record.php'; ?>
        </div>
        <div id="activities" class="section">
            <?php include 'sections/activities.php'; ?>
        </div>
        <div id="results" class="section">
            <?php include 'sections/results.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../assets/js/teacher.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();

            // Simple Tab Switcher Logic (in case teacher.js is missing or broken)
            const links = document.querySelectorAll('.nav-link[data-section]');
            const sections = document.querySelectorAll('.section');

            links.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();

                    // Remove active from all links
                    links.forEach(l => l.classList.remove('active'));
                    // Add active to clicked link
                    link.classList.add('active');

                    // Hide all sections
                    sections.forEach(s => s.classList.remove('active'));

                    // Show target section
                    const targetId = link.getAttribute('data-section');
                    const targetSection = document.getElementById(targetId);
                    if (targetSection) targetSection.classList.add('active');
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>

</html>