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

<div class="container my-5">
    <div class="row justify-content-center mb-3">
        <div class="col-12 col-lg-10">
            <a href="index.php" class="btn btn-outline-secondary">Back to Listings</a>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="card shadow border-0 rounded-3 overflow-hidden">

                <div class="row g-0">
                    <div class="col-md-5 bg-light d-flex align-items-center justify-content-center p-4">
                        <img src='../listing/getImage.php?id=<?php echo htmlspecialchars($product['product_id']); ?>'
                            alt='<?php echo htmlspecialchars($product['title']); ?>'
                            class="img-fluid rounded-3" style="max-height: 300px; object-fit: contain;">
                    </div>

                    <div class="col-md-7">
                        <div class="card-header bg-primary text-white py-3">
                            <h2 class="mb-0 fs-4"><?php echo htmlspecialchars($product['title']); ?></h2>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-secondary px-3 py-2"><?php echo htmlspecialchars($product['category']); ?></span>
                                    <span class="fs-3 fw-bold text-primary">R <?php echo number_format($product['price'], 2); ?></span>
                                </div>

                                <div class="mb-3">
                                    <h5 class="text-muted mb-2">Description</h5>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                                </div>

                                <div class="d-flex align-items-center mb-3">
                                    <div>
                                        <small class="text-muted">Seller</small>
                                        <p class="mb-0 fw-semibold"><?php echo htmlspecialchars($seller['name']) . " " . htmlspecialchars($seller['surname']); ?></p>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center">
                                    <div>
                                        <small class="text-muted">Status</small>
                                        <p class="mb-0 fw-semibold">
                                            <span class="badge bg-<?php echo $product['status'] == 'Active' ? 'success' : 'danger'; ?>">
                                                <?php echo htmlspecialchars($product['status']); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <?php if ($status == 'Pending payment'): ?>
                                <form action="../payment/create.php" method="post" class="mt-4">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                    <input type="hidden" name="price" value="<?php echo $product['price']; ?>">
                                    <div class="d-flex justify-content-center">
                                        <button type="submit" class="btn btn-success w-50">Make Payment</button>
                                    </div>
                                </form>
                            <?php elseif ($product['status'] == 'Active' && $product['seller_id'] != $_SESSION['User_ID']): ?>
                                <a href="../order/create.php?product_id=<?php echo htmlspecialchars($product['product_id']); ?>"
                                    class="btn btn-primary btn-lg w-100">Order Now</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>