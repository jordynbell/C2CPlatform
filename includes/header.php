<?php
if (!isset($_SESSION)) {
    session_start();
}

// Define $basePath before using it in the redirect
$isProduction = (getenv('AWS_ENVIRONMENT') !== false
    || getenv('EB_ENVIRONMENT') !== false
    || file_exists('/var/www/html'));
$basePath = $isProduction ? '' : '/C2CPlatform/public';

// Authentication logic
$current_path = $_SERVER['PHP_SELF'];
$auth_paths = ['/auth/login.php', '/auth/register.php', '/C2CPlatform/public/auth/login.php', '/C2CPlatform/public/auth/register.php'];
$public_paths = ['/about.php', '/C2CPlatform/public/about.php', '/index.php', '/C2CPlatform/public/index.php', '/privacy.php', '/C2CPlatform/public/privacy.php'];

$is_public_page = false;

// Check if the current path is in the public paths or auth paths
foreach (array_merge($auth_paths, $public_paths) as $path) {
  if (strpos($current_path, $path) !== false) {
    $is_public_page = true;
    break;
  }
}

// Check if the user is logged in and if the page is not public
if (!isset($_SESSION["User_ID"]) && !$is_public_page) {
  header("Location: {$basePath}/auth/login.php");
  exit;
}
?>

<!-- Implementation of the header, including css dependencies and the referencing of navigation.php -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Squito'; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <?php

    $isProduction = (getenv('AWS_ENVIRONMENT') !== false
        || getenv('EB_ENVIRONMENT') !== false
        || file_exists('/var/www/html'));
    $basePath = $isProduction ? '' : '/C2CPlatform/public';
    ?>
    <link rel="icon" href="<?php echo $basePath; ?>/assets/images/favicon.ico" type="image/x-icon">
    <link href="<?php echo $basePath; ?>/assets/css/site.css" rel="stylesheet">

</head>

<body>
    <?php require_once __DIR__ . '/navigation.php'; ?>