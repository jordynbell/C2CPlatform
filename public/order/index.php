<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "My Orders - Squito";

$user_id = $_SESSION['User_ID'];

$stmt = $conn->prepare('SELECT * FROM `order` WHERE customer_id = ?');
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container">
    <h1 class="text-center">My Orders</h1>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered" border="1">
            <tr>
                <th>Order Number</th>
                <th>Order Date</th>
                <th>Price</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo $order['order_id']; ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td><?php echo 'R ' . number_format($order['price'],2); ?></td>
                    <td><?php echo $order['status']; ?></td>
                    <td>
                        <a href="view.php?id=<?php echo $order['product_id']; ?>" class="btn btn-primary">View</a>
                        <?php if ($order['status'] == 'Pending payment'): ?>
                            <a href="cancel.php?id=<?php echo $order['order_id']; ?>" class="btn btn-danger">Cancel</a>
                        <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>


<?php
require_once __DIR__ . '/../../includes/footer.php';
?>