<?php

require_once __DIR__ . '/../lib/db.php';

if (!isset($_SESSION)) {
  session_start();
}

// Set base path to ensure it works whilst hosted on AWS and locally

if (!isset($basePath)) {
  $isProduction = (getenv('AWS_ENVIRONMENT') !== false
    || getenv('EB_ENVIRONMENT') !== false
    || file_exists('/var/www/html'));
  $basePath = $isProduction ? '' : '/C2CPlatform/public';
}

$current_path = $_SERVER['PHP_SELF'];
$auth_paths = ['/auth/login.php', '/auth/register.php', '/C2CPlatform/public/auth/login.php', '/C2CPlatform/public/auth/register.php'];
$public_paths = ['/about.php', '/C2CPlatform/public/about.php', '/index.php', '/C2CPlatform/public/index.php'];

$is_public_page = false;

// Check if the current path is in the public paths, otherwise check if it is in the auth paths

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

$user_id = isset($_SESSION["User_ID"]) ? $_SESSION["User_ID"] : null;
$role = isset($_SESSION["Role"]) ? $_SESSION["Role"] : null;
?>

<nav class="navbar fixed-top navbar-expand-lg navbar-dark navbar-custom">
  <div class="container-fluid">

    <a class="navbar-brand" href="<?php echo $basePath; ?>/index.php">Squito</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">


        <li class="nav-item">
          <a class="nav-link" href="<?php echo $basePath; ?>/index.php">Home</a>
        </li>
        <?php if (isset($_SESSION["User_ID"])): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="listingsDropdown" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              Listings
            </a>
            <ul class="dropdown-menu" aria-labelledby="listingsDropdown">
              <li><a class="dropdown-item" href="<?php echo $basePath; ?>/listing/index.php">View Listings</a></li>
              <li><a class="dropdown-item" href="<?php echo $basePath; ?>/listing/create.php">Create Listing</a></li>
              <li><a class="dropdown-item" href="<?php echo $basePath; ?>/listing/seller_index.php">My Listings</a></li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $basePath; ?>/order/index.php">My Orders</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="listingsDropdown" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              Address Book
            </a>
            <ul class="dropdown-menu" aria-labelledby="addressesDropdown">
              <li><a class="dropdown-item" href="<?php echo $basePath; ?>/address/index.php">View Addresses</a></li>
              <li><a class="dropdown-item" href="<?php echo $basePath; ?>/address/create.php">Create Addresses</a></li>
            </ul>
          </li>

          <?php if ($role === 'Admin'): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                Admin
              </a>
              <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                <li><a class="dropdown-item" href="<?php echo $basePath; ?>/user/index.php">Manage Users</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li class="dropdown-header">Reports</li>
                <li><a class="dropdown-item" href="<?php echo $basePath; ?>/reports/listings.php">Listings Report</a></li>
                <li><a class="dropdown-item" href="<?php echo $basePath; ?>/reports/sales.php">Sales Report</a></li>
                <li><a class="dropdown-item" href="<?php echo $basePath; ?>/reports/shipments.php">Shipments Report</a></li>
              </ul>
            </li>
          <?php endif; ?>
        <?php endif; ?>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $basePath; ?>/about.php">About</a>
        </li>
      </ul>

      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <?php if (isset($_SESSION["User_ID"])): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $basePath; ?>/auth/logout.php">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $basePath; ?>/auth/login.php">Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $basePath; ?>/auth/register.php">Register</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>