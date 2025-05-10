<?php

require_once __DIR__ . '/../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$auth_pages = ['login.php', 'register.php'];

if (!isset($_SESSION["User_ID"]) && !in_array($current_page, $auth_pages)) {
    header("Location: ../auth/login.php");
    exit;
}

// Get user info only if logged in
$user_id = isset($_SESSION["User_ID"]) ? $_SESSION["User_ID"] : null;
$role = isset($_SESSION["Role"]) ? $_SESSION["Role"] : null;

?>

<nav class="navbar fixed-top navbar-expand-lg navbar-dark navbar-custom">
  <div class="container-fluid">
    <a class="navbar-brand" href="/C2CPlatform/public/index.php">Squito</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <?php if (isset($_SESSION["User_ID"])): ?>
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">        
        <li class="nav-item">
          <a class="nav-link" href="/C2CPlatform/public/index.php">Home</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="listingsDropdown"
             role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Listings
          </a>
          <ul class="dropdown-menu" aria-labelledby="listingsDropdown">
            <li><a class="dropdown-item" href="/C2CPlatform/public/listing/index.php">View Listings</a></li>
            <li><a class="dropdown-item" href="/C2CPlatform/public/listing/create.php">Create Listing</a></li>
            <li><a class="dropdown-item" href="/C2CPlatform/public/listing/edit.php">Edit Listing</a></li>
            <li><a class="dropdown-item" href="/C2CPlatform/public/listing/seller_index.php">My Listings</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/C2CPlatform/public/order/index.php">My Orders</a>
        </li>
        <?php endif; ?>
        <?php if ($role === 'Admin'): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="adminDropdown"
             role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Admin
          </a>
          <ul class="dropdown-menu" aria-labelledby="adminDropdown">
            <li><a class="dropdown-item" href="/C2CPlatform/public/manage/users.php">Manage Users</a></li>
            <li><hr class="dropdown-divider"></li>
            <li class="dropdown-header">Reports</li>
            <li><a class="dropdown-item" href="/C2CPlatform/public/reports/listings.php">Listings Report</a></li>
            <li><a class="dropdown-item" href="/C2CPlatform/public/reports/sales.php">Sales Report</a></li>
            <li><a class="dropdown-item" href="/C2CPlatform/public/reports/shipments.php">Shipments Report</a></li>
          </ul>
        </li>
        <?php endif; ?>
        <?php if (isset($_SESSION["User_ID"])): ?>
        <li class="nav-item">
          <a class="nav-link" href="/C2CPlatform/public/auth/logout.php">Logout</a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>