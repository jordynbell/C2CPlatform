<?php
// Initialise database connection parameters
if (getenv('DB_HOST')) {
    $servername = getenv('DB_HOST');
    $port       = getenv('DB_PORT') ?: 3306;
    $username   = getenv('DB_USER');
    $password   = getenv('DB_PASS');
    $dbname     = getenv('DB_NAME');

} elseif (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';

} else {
    throw new RuntimeException("No database config: set DB_HOST or add config.local.php");
}