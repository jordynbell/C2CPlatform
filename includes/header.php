<?php
if (!isset($_SESSION)) {
    session_start();
}
?>

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