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
    $user_id = $_POST["user_id"] ?? null;
    
    if ($user_id) {
        // Check for products linked to the user
        $no_product_stmt = $conn->prepare('SELECT * FROM product WHERE seller_id = ? LIMIT 1');
        $no_product_stmt->bind_param("i", $user_id);
        $no_product_stmt->execute();
        $no_product_result = $no_product_stmt->get_result();
        if ($no_product_result->num_rows > 0) {
            $no_product_result->close();

            // Set toast error messages
            $_SESSION['toast_message'] = "Cannot delete user with products.";
            $_SESSION['toast_type'] = "warning";

            $conn->close();

            // Redirect if accessed directly without POST
            header("Location: index.php");
            exit;
        }
        $no_product_stmt->close();
    
        // Check for orders linked to the user
        $no_order_stmt = $conn->prepare('SELECT * FROM `order` WHERE customer_id = ? LIMIT 1');
        $no_order_stmt->bind_param("i", $user_id);
        $no_order_stmt->execute();
        $no_order_result = $no_order_stmt->get_result();
        if ($no_order_result->num_rows > 0) {
            $no_order_stmt->close();

            // Set toast error messages
            $_SESSION['toast_message'] = "Cannot delete user with orders.";
            $_SESSION['toast_type'] = "warning";

            $conn->close();

            header("Location: index.php");
            exit;
        }
        $no_order_stmt->close();
    
        // Check for addresses linked to the user
        $no_address_stmt = $conn->prepare('SELECT * FROM address WHERE user_id = ? LIMIT 1');
        $no_address_stmt->bind_param("i", $user_id);
        $no_address_stmt->execute();
        $no_address_result = $no_address_stmt->get_result();
        if ($no_address_result->num_rows > 0) {
            $no_address_stmt->close();

            // Set toast error messages
            $_SESSION['toast_message'] = "Cannot delete user with addresses.";
            $_SESSION['toast_type'] = "warning";

            $conn->close();

            header("Location: index.php");
            exit;
        }
        $no_address_stmt->close();
    
        // Delete the user
        $stmt = $conn->prepare('DELETE FROM user WHERE user_id = ?');
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // Set toast success messages
        $_SESSION['toast_message'] = "User deleted successfully.";
        $_SESSION['toast_type'] = "success";

        $conn->close();
        
        header("Location: index.php");
        exit;
    } else {

        // Set toast error messages
        $_SESSION['toast_message'] = "Invalid user ID.";
        $_SESSION['toast_type'] = "warning";

        $conn->close();

        header("Location: index.php");
        exit;
    }
} else {
    // Set toast error messages
    $_SESSION['toast_message'] = "Invalid request method.";
    $_SESSION['toast_type'] = "warning";

    $conn->close();
    
    // Redirect if accessed directly without POST
    header("Location: index.php");
    exit;
}
?>