<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "View Order Details - Squito";

$stmt = $conn->prepare('SELECT * FROM product where product_id = ?');

require_once __DIR__ . '/../../includes/header.php';
?>



<?php require_once __DIR__ . '/../../includes/footer.php'; ?>