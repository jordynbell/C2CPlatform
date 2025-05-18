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

require_once __DIR__ . '/../../includes/header.php';

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

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 col-xl-6 mx-auto">
            <div class="card shadow-sm border-0 rounded overflow-hidden bg-light text-dark ">
                <div class="card-header text-center bg-primary text-white">
                    <h3 class="mb-0">Product Details</h3>
                </div>
                <div class="text-center mt-4">
                    <img src='../listing/getImage.php?id=<?php echo htmlspecialchars($product['product_id']); ?>'
                        alt='Product Image' class='img-thumbnail' style='width: 8rem; height: 8rem;'>
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
<div class="text-center mt-4">
    <a href="listings.php" class="btn btn-primary">Back to My Orders</a>
</div>
<?php

require_once __DIR__ . '/../../includes/footer.php';

?>