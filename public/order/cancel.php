<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['User_ID'];

$order_id = $_GET['id'] ?? null;
if ($order_id === null) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare('UPDATE `order` SET status = "Cancelled" WHERE order_id = ? AND customer_id = ?');
$stmt->bind_param("ii", $order_id, $user_id);

if ($stmt->execute()) {
    $stmt->close();

    $stmt = $conn->prepare('UPDATE shipment SET delivery_status = "Cancelled" WHERE order_id = ?');
    $stmt->bind_param("i", $order_id);
    if ($stmt->execute()) {
        $stmt->close();
    } else {
        $_SESSION['error'] = "Failed to cancel shipment. Please try again.";
        $stmt->close();
        header("Location: index.php");
        exit;
    }

    header("Location: index.php");
    exit;
} else {
    $_SESSION['error'] = "Failed to cancel order. Please try again.";
    header("Location: index.php");
    exit;
}

?>