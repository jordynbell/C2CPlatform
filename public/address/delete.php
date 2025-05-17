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
$address_id = $_GET['id'] ?? null;
if ($address_id === null) {
    header("Location: index.php");
    exit;
}

$isActive = 0;

$stmt = $conn->prepare('UPDATE address SET isActive = ? WHERE address_id = ? AND user_id = ?');
$stmt->bind_param("iii", $isActive, $address_id, $user_id);
if ($stmt->execute()) {
    header("Location: index.php");
    exit;
} else {
    $_SESSION['error'] = "Failed to delete address. Please try again.";
}
$stmt->close();
?>