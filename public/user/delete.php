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
    $user_id = $_POST["user_id"] ?? null;
    
    if ($user_id) {
        // Check for products linked to the user
        $no_product_stmt = $conn->prepare('SELECT * FROM product WHERE seller_id = ? LIMIT 1');
        $no_product_stmt->bind_param("i", $user_id);
        $no_product_stmt->execute();
        $no_product_result = $no_product_stmt->get_result();
        if ($no_product_result->num_rows > 0) {
            echo "<script>alert('Cannot delete user with listings.');</script>";
            $no_product_stmt->close();
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
            echo "<script>alert('Cannot delete user with orders.');</script>";
            $no_order_stmt->close();
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
            echo "<script>alert('Cannot delete user with addresses.');</script>";
            $no_address_stmt->close();
            header("Location: index.php");
            exit;
        }
        $no_address_stmt->close();
    
        // Delete the user
        $stmt = $conn->prepare('DELETE FROM user WHERE user_id = ?');
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        
        header("Location: index.php");
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
} else {
    // Redirect if accessed directly without POST
    header("Location: index.php");
    exit;
}
?>