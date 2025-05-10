<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "Cancel Order - Squito";

$user_id = $_SESSION['User_ID'];

$order_id = $_GET['id'] ?? null;
if ($order_id === null) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare('UPDATE `order` SET status = "Cancelled" WHERE order_id = ? AND customer_id = ?');
$stmt->bind_param("ii", $order_id, $user_id);

if ($stmt->execute())
{
    header("Location: index.php");
    exit;
} else {
    $_SESSION['error'] = "Failed to cancel order. Please try again.";
}


require_once __DIR__ . '/../../includes/header.php';

?>