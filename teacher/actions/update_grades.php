<?php
require_once '../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

$student_id = $data['student_id'];
$section_id = $data['section_id'];
$subject_id = $data['subject_id'];
$quarter = $data['quarter'];
$school_year = $data['school_year'];
$ww = json_encode($data['ww']);
$pt = json_encode($data['pt']);
$qa = $data['qa'];
$final = $data['final'];

// Check if record exists
$stmt = $pdo->prepare("SELECT grade_id FROM grades WHERE student_id=? AND section_id=? AND subject_id=? AND quarter=? AND school_year=?");
$stmt->execute([$student_id, $section_id, $subject_id, $quarter, $school_year]);
$exists = $stmt->fetchColumn();

if ($exists) {
    $stmt = $pdo->prepare("UPDATE grades SET ww=?, pt=?, qa=?, final_grade=?, updated_at=NOW() WHERE grade_id=?");
    $stmt->execute([$ww, $pt, $qa, $final, $exists]);
} else {
    $stmt = $pdo->prepare("INSERT INTO grades (student_id, section_id, subject_id, quarter, school_year, ww, pt, qa, final_grade)
                           VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$student_id, $section_id, $subject_id, $quarter, $school_year, $ww, $pt, $qa, $final]);
}

echo json_encode(['success' => true]);
