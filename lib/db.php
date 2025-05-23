<?php 
require_once __DIR__ . '/../config/config.php';

// Create a connection to the db

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}