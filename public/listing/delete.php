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

    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;
    $seller_id = $_SESSION['User_ID'];

    if (!$product_id) {
        // Set toast error messages
        $_SESSION['toast_message'] = "Product ID is required.";
        $_SESSION['toast_type'] = "danger";

        $conn->close();

        header("Location: seller_index.php");
        exit;
    }

    $check_orders = $conn->prepare("SELECT order_id FROM `order` WHERE product_id = ? AND status IN ('Pending', 'Processing')");
    $check_orders->bind_param("i", $product_id);
    $check_orders->execute();
    $result = $check_orders->get_result();

    if ($result->num_rows > 0) {
        $check_orders->close();

        // Set toast error messages
        $_SESSION['toast_message'] = "Cannot delete the listing as there are pending or processing orders.";
        $_SESSION['toast_type'] = "danger";

        $conn->close();

        header("Location: seller_index.php");
        exit;
    }

    // Update the product status to "Deleted"
    $stmt = $conn->prepare('UPDATE product SET status = "Deleted" WHERE product_id = ? AND seller_id = ?');
    $stmt->bind_param("ii", $product_id, $seller_id);

    if ($stmt->execute()) {
        $stmt->close();

        // Set toast success messages
        $_SESSION['toast_message'] = "Listing deleted successfully.";
        $_SESSION['toast_type'] = "success";
    } else {
        $stmt->close();

        // Set toast error messages
        $_SESSION['toast_message'] = "Error deleting the listing.";
        $_SESSION['toast_type'] = "danger";
    }

    $conn->close();

    // Redirect back to the seller index page
    header("Location: seller_index.php");
    exit;
} else {
    // Set toast error messages
    $_SESSION['toast_message'] = "Invalid request method.";
    $_SESSION['toast_type'] = "danger";

    $conn->close();

    // If accessed directly without POST request, redirect to seller index
    header("Location: seller_index.php");
    exit;
}

?>