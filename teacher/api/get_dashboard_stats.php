<?php
// teacher/api/get_dashboard_stats.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']))
    exit(json_encode(['students' => 0, 'sections' => 0, 'activities' => 0, 'recent' => []]));

try {
    // 1. Get Teacher ID
    $stmt = $pdo->prepare("SELECT teacher_id FROM teachers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $teacher_id = $stmt->fetchColumn();

    if (!$teacher_id)
        throw new Exception("Teacher not found");

    // 2. COUNTS (Same as before)
    $sqlStudents = "
        SELECT COUNT(DISTINCT e.student_id) FROM subject_assignments sa
        JOIN enrollments e ON sa.section_id = e.section_id AND sa.school_year_id = e.school_year_id
        WHERE sa.teacher_id = ? AND e.status = 'active'";
    $stmt = $pdo->prepare($sqlStudents);
    $stmt->execute([$teacher_id]);
    $total_students = $stmt->fetchColumn();

    $sqlSections = "SELECT COUNT(DISTINCT section_id) FROM subject_assignments WHERE teacher_id = ? AND is_active = 1";
    $stmt = $pdo->prepare($sqlSections);
    $stmt->execute([$teacher_id]);
    $total_sections = $stmt->fetchColumn();

    $sqlActivities = "
        SELECT COUNT(a.activity_id) FROM activities a
        JOIN subject_assignments sa ON a.assignment_id = sa.assignment_id
        WHERE sa.teacher_id = ? AND a.is_published = 1";
    $stmt = $pdo->prepare($sqlActivities);
    $stmt->execute([$teacher_id]);
    $total_activities = $stmt->fetchColumn();

    // 3. FETCH RECENT ACTIVITIES (New Feature)
    // Gets the last 5 activities you created/updated
    $sqlRecent = "
        SELECT 
            a.title, 
            a.activity_type, 
            a.due_date,
            s.subject_name,
            sec.grade_level,
            sec.section_name
        FROM activities a
        JOIN subject_assignments sa ON a.assignment_id = sa.assignment_id
        JOIN subjects s ON sa.subject_id = s.subject_id
        JOIN sections sec ON sa.section_id = sec.section_id
        WHERE sa.teacher_id = ? AND a.is_published = 1
        ORDER BY a.created_at DESC LIMIT 5
    ";
    $stmt = $pdo->prepare($sqlRecent);
    $stmt->execute([$teacher_id]);
    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'students' => $total_students,
        'sections' => $total_sections,
        'activities' => $total_activities,
        'recent' => $recent
    ]);

} catch (Exception $e) {
    echo json_encode(['students' => 0, 'sections' => 0, 'activities' => 0, 'recent' => []]);
}
?>