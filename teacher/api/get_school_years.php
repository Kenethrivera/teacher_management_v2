<?php

require_once '../../middleware/auth.php';
require_once '../../config/database.php';

$stmt = $pdo->query("
    SELECT id, school_year
    FROM school_years
    ORDER BY school_year DESC
");

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
