<?php
// teacher/actions/release_grades.php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['user_id']) || empty($data['students'])) {
    echo json_encode(['success' => false, 'message' => 'No data received.']);
    exit;
}

try {
    $students = $data['students']; // Array of {id: 1, grade: 85.5}
    $quarter = $data['quarter'];
    $release_type = $data['release_type'];

    $pdo->beginTransaction();

    // SQL: Create record if missing, Update if exists.
    // We update 'is_released', 'release_type', AND 'final_grade' to ensure accuracy.
    $sql = "INSERT INTO computed_grades 
            (subject_enrollment_id, quarter, final_grade, is_released, release_type, released_at, computed_at)
            VALUES (?, ?, ?, 1, ?, NOW(), NOW()) 
            ON DUPLICATE KEY UPDATE 
            final_grade = VALUES(final_grade), -- Update the grade to match UI
            is_released = 1, 
            release_type = VALUES(release_type), 
            released_at = NOW()";

    $stmt = $pdo->prepare($sql);

    $count = 0;
    foreach ($students as $st) {
        $grade = is_numeric($st['grade']) ? $st['grade'] : 0.00;

        // Execute: subject_enrollment_id, quarter, grade, release_type
        $stmt->execute([$st['id'], $quarter, $grade, $release_type]);
        $count += $stmt->rowCount();
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'updated' => $count]);

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>