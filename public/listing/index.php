<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "View Listings - Squito";

$stmt = $conn->prepare('SELECT product_id, title, description, category, price, status, image FROM product WHERE status = "Active" AND seller_id != ?');
$stmt->bind_param("i", $_SESSION['User_ID']);
$stmt->execute();
$rows = $stmt->get_result();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container">
    <h1 class="text-center">View Listings</h1>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered" border="1">
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Category</th>
                <th>Price</th>
                <th>Image</th>
                <th>Action</th>
            </tr>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo 'R ' . htmlspecialchars(number_format($row['price'], 2)); ?></td>
                    <td><img src="../listing/getImage.php?id=<?php echo htmlspecialchars($row['product_id']); ?>" alt="Product Image" class="img-thumbnail" style="width: 100px; height: 100px;"></td>
                    <td><a href="../order/create.php?product_id=<?php echo htmlspecialchars($row['product_id']); ?>" class="btn btn-primary">Order</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>