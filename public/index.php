<?php 

if (!isset($_SESSION)) {
    session_start();
}

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["Email"])) {
    header("Location: auth/login.php");
    exit;
}

$pageTitle = "Home - Squito";

require_once __DIR__ . '/../includes/header.php';

?>

    <h1>My First PHP Page</h1>
    <?php
        echo "Welcome to Squito!";
    ?>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>