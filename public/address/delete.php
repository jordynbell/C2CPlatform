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

$user_id = $_SESSION['User_ID'];
$address_id = $_GET['id'] ?? null;
if ($address_id === null) {
    // Set toast error messages
    $_SESSION['toast_message'] = "Invalid address ID.";
    $_SESSION['toast_type'] = "danger";

    $conn->close();
    
    header("Location: index.php");
    exit;
}

$isActive = 0;

$stmt = $conn->prepare('UPDATE address SET isActive = ? WHERE address_id = ? AND user_id = ?');
$stmt->bind_param("iii", $isActive, $address_id, $user_id);
if ($stmt->execute()) {
    // Set toast success messages
    $_SESSION['toast_message'] = "Address deleted successfully!";
    $_SESSION['toast_type'] = "success";

    $stmt->close();

    $conn->close();

    header("Location: index.php");
    exit;
} else {
    $stmt->close();

    $conn->close();

    // Set toast error messages
    $_SESSION['toast_message'] = "Failed to delete address. Please try again.";
    $_SESSION['toast_type'] = "danger";
    
}

?>