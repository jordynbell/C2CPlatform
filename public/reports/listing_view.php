<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION['Role'] != 'Admin') {
    header("Location: ../index.php");
    exit;
}

$pageTitle = "View Listing - Squito";

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$product_id = $_GET['id'];

$stmt = $conn->prepare('SELECT * FROM product WHERE product_id = ? LIMIT 1');
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "<div class='alert alert-danger'>Product not found.</div>";
    exit;
}

$sellerId = $product['seller_id'];
$stmt = $conn->prepare('SELECT name, surname FROM user WHERE user_id = ? LIMIT 1');
$stmt->bind_param('i', $sellerId);
$stmt->execute();
$result = $stmt->get_result();
$seller = $result->fetch_assoc();

if (!$seller) {
    echo "<div class='alert alert-danger'>Seller not found.</div>";
    exit;
}

$stmt->close();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container my-5">
    <div class="row justify-content-center mb-3">
        <div class="col-12 col-lg-10">
            <a href="listings.php" class="btn btn-outline-secondary">Back to Listings</a>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php

require_once __DIR__ . '/../../includes/footer.php';

?>