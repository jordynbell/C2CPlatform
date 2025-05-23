<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    // Set toast error messages
    $_SESSION['toast_message'] = "Please log in to access this page.";
    $_SESSION['toast_type'] = "warning";

    $conn->close();

    // Redirect to login page
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['User_ID'];

$order_id = $_GET['id'] ?? null;
if ($order_id === null) {
    // Set toast error messages
    $_SESSION['toast_message'] = "Invalid order ID.";
    $_SESSION['toast_type'] = "danger";

    $conn->close();

    // Redirect to index page
    header("Location: index.php");
    exit;
}

// Update the order to cancelled if the order_id is valid and the user_id matches
$stmt = $conn->prepare('UPDATE `order` SET status = "Cancelled" WHERE order_id = ? AND customer_id = ?');
$stmt->bind_param("ii", $order_id, $user_id);

if ($stmt->execute()) {
    $stmt->close();

    // Update the shipment status to cancelled
    $stmt = $conn->prepare('UPDATE shipment SET delivery_status = "Cancelled" WHERE order_id = ?');
    $stmt->bind_param("i", $order_id);
    if ($stmt->execute()) {

        $stmt->close();

        // Set toast success messages
        $_SESSION['toast_message'] = "Order cancelled successfully.";
        $_SESSION['toast_type'] = "success";
    } else {
        $stmt->close();

        // Set toast error messages
        $_SESSION['toast_message'] = "Failed to cancel shipment. Please try again.";
        $_SESSION['toast_type'] = "danger";
    }

    $conn->close();

    // Redirect to index page
    header("Location: index.php");
    exit;
} else {
    $stmt->close();

    // Set toast error messages
    $_SESSION['toast_message'] = "Failed to cancel order. Please try again.";
    $_SESSION['toast_type'] = "danger";

    $conn->close();

    // Redirect to index page
    header("Location: index.php");
    exit;
}

?>