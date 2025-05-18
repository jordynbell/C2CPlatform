<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION['Role'] != 'Admin') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;
    $seller_id = $_SESSION['User_ID'];

    if (!$product_id) {
        $_SESSION['error'] = "Product ID is required";
        header("Location: listings.php");
        exit;
    }

    $check_orders = $conn->prepare("SELECT order_id FROM `order` WHERE product_id = ? AND status IN ('Pending', 'Processing')");
    $check_orders->bind_param("i", $product_id);
    $check_orders->execute();
    $result = $check_orders->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "This product cannot be deleted because it has pending orders.";
        header("Location: listings.php");
        exit;
    }

    // Update the product status to "Deleted"
    $stmt = $conn->prepare('UPDATE product SET status = "Deleted" WHERE product_id = ?');
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Listing deleted successfully";
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }

    // Redirect back to the seller index page
    header("Location: listings.php");
    exit;
} else {
    // If accessed directly without POST request, redirect to seller index
    header("Location: listings.php");
    exit;
}