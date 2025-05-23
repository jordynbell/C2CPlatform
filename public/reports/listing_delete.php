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

if ($_SESSION['Role'] != 'Admin') {
    // Set toast error messages
    $_SESSION['toast_message'] = "You do not have permission to access this page.";
    $_SESSION['toast_type'] = "warning";

    $conn->close();

    // Redirect to home page
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;
    $seller_id = $_SESSION['User_ID'];

    if (!$product_id) {
        // Set toast error messages
        $_SESSION['toast_message'] = "Invalid product ID.";
        $_SESSION['toast_type'] = "warning";

        $conn->close();

        // Redirect back to the seller index page
        header("Location: listings.php");
        exit;
    }

    // Check if the order exists for the product and it's status is "Payment pending"
    $check_orders = $conn->prepare("SELECT order_id FROM `order` WHERE product_id = ? AND status = 'Payment pending'");
    $check_orders->bind_param("i", $product_id);
    $check_orders->execute();
    $result = $check_orders->get_result();
    $check_orders->close();

    if ($result->num_rows > 0) {
        // Set toast error messages
        $_SESSION['toast_message'] = "Cannot delete the listing. There are pending or processing orders associated with this product.";
        $_SESSION['toast_type'] = "warning";

        $conn->close();

        // Redirect back to the listings page
        header("Location: listings.php");
        exit;
    }

    // Update the product status to "Deleted"
    $stmt = $conn->prepare('UPDATE product SET status = "Deleted" WHERE product_id = ?');
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        // Set toast success messages
        $_SESSION['toast_message'] = "Product deleted successfully.";
        $_SESSION['toast_type'] = "success";
    } else {
        // Set toast error messages
        $_SESSION['toast_message'] = "Failed to delete the product.";
        $_SESSION['toast_type'] = "danger";
    }

    $stmt->close();

    $conn->close();

    // Redirect back to the seller index page
    header("Location: listings.php");
    exit;
} else {
    // Set toast error messages
    $_SESSION['toast_message'] = "Invalid request method.";
    $_SESSION['toast_type'] = "warning";

    $conn->close();
    
    // If accessed directly without POST request, redirect to seller index
    header("Location: listings.php");
    exit;
}