<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['order_id']) && isset($_GET['id'])) {
    $order_stmt = $conn->prepare('SELECT order_id FROM `order` WHERE product_id = ? AND status = "Pending payment" LIMIT 1');
    $order_stmt->bind_param('i', $_GET['id']);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();
    if ($order_result->num_rows > 0) {
        $order_data = $order_result->fetch_assoc();
        $order_id = $order_data['order_id'];
    } else {
        $order_id = 0;
    }
    $order_stmt->close();
} else {
    $order_id = $_GET['order_id'] ?? 0;
}

$pageTitle = "View Order Details - Squito";
$sellerId = null;
$status = isset($_GET['status']) ? $_GET['status'] : null;

$stmt = $conn->prepare('SELECT * FROM product where product_id = ? LIMIT 1');
$stmt->bind_param('i', $_GET['id']);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

$sellerId = $product['seller_id'];

$stmt = $conn->prepare('SELECT name, surname FROM user where user_id = ? LIMIT 1');
$stmt->bind_param('i', $sellerId);
$stmt->execute();
$result = $stmt->get_result();
$seller = $result->fetch_assoc();

$stmt->close();
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 col-xl-6 mx-auto">
            <div class="card shadow-sm border-0 rounded overflow-hidden bg-light text-dark ">
                <div class="card-header text-center bg-primary text-white">
                    <h3 class="mb-0">Product Details</h3>
                </div>
                <div class="card-body">
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <b>Title:</b> <?= htmlspecialchars($product['title']) ?>
                        </div>
                        <div class="col-md-6">
                            <b>Category:</b> <?= htmlspecialchars($product['category']) ?>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <b>Description:</b> <?= htmlspecialchars($product['description']) ?>
                        </div>
                        <div class="col-md-6">
                            <b>Seller:</b>
                            <?= htmlspecialchars($seller['name']) . " " . htmlspecialchars($seller['surname']) ?>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                        </div>
                        <div class="col-md-6">
                            <b>Price:</b> R <?= number_format($product['price'], 2) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($status == 'Pending payment'): ?>
    <div class="text-center mt-4">
        <div class="text-center mt-4">
            <div class="text-center mt-4">
                <form action="../payment/create.php" method="post">
                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                    <input type="hidden" name="price" value="<?= $product['price'] ?>">
                    <button type="submit" class="btn btn-success">Make Payment</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-primary">Back to My Orders</a>
    </div>
    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>