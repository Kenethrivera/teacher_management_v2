<?php
require_once '../../config/database.php';
require_once '../../middleware/auth.php';

$data = json_decode(file_get_contents("php://input"), true);

$enrollment_id = $data['enrollment_id'];
$subject_id = $data['subject_id'];
$quarter = $data['quarter'];

$ww = json_encode($data['ww']);
$pt = json_encode($data['pt']);
$qa = $data['qa'];

// FINAL GRADE CALCULATION (simple placeholder)
$final = $data['final'] ?? null;

// UPSERT
$sql = "
INSERT INTO grades (enrollment_id, subject_id, quarter, ww, pt, qa, final_grade)
VALUES (?, ?, ?, ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE
    ww = VALUES(ww),
    pt = VALUES(pt),
    qa = VALUES(qa),
    final_grade = VALUES(final_grade)
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $enrollment_id,
    $subject_id,
    $quarter,
    $ww,
    $pt,
    $qa,
    $final
]);

echo json_encode(['status' => 'success']);
