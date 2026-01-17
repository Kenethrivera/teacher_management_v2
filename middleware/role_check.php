<?php
if (!isset($required_role)) {
    die('Role not specified');
}

if ($_SESSION['role'] !== $required_role) {
    header('Location: ../login.php');
    exit;
}

