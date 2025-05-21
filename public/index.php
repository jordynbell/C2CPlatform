<!-- 
What do add to landing page:
1. Create a hero section with a background image and a welcome message.
2. Second section can have the benefits of using Squito.
3. Display a few products, perhaps usig sql ... limit 3-5
4. Reviews section with user testimonials.
5. FAQ using bootstrap accordion.
6. Get started section prompting user to register.

Referenced links: 

https://fontawesome.com/icons/

https://stackoverflow.com/questions/580639/how-to-randomly-select-rows-in-sql

-->

<?php

require_once __DIR__ . '/../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (isset($_SESSION["User_ID"])) {
    $user_id = $_SESSION["User_ID"];
    $stmt = $conn->prepare("SELECT * FROM user WHERE User_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $result = $result->fetch_assoc();
    } else {
        header("Location: auth/login.php");
        exit;
        $stmt->close();
    }
    $stmt->close();
}

$stmt = $conn->prepare('SELECT * FROM product WHERE status != "Sold" ORDER BY RAND() LIMIT 3');
$stmt->execute();
$products_results = $stmt->get_result();
if ($products_results->num_rows > 0) {
    $products = $products_results->fetch_all(MYSQLI_ASSOC);
} else {
    $products = [];
}

$pageTitle = "Home - Squito";

require_once __DIR__ . '/../includes/header.php';

?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<section class="hero d-flex justify-content-center align-items-center text-center"
    style="background-image: linear-gradient(to right, rgba(var(--bs-primary-rgb), 0.3), rgba(var(--bs-secondary-rgb), 0.3), rgba(var(--bs-primary-rgb), 0.3)), url('assets/images/hero-img.jpg');min-height: 100vh; background-size: cover; background-position: center; position: relative;margin-top: -56px;padding-top: 56px;">
    <div class="hero-content">
        <h1 class="display-2 fw-bold text-white">Discover,</h1>
        <h1 class="display-2 fw-bold text-white">buy, or sell,</h1>
        <h1 class="display-2 fw-bold text-white mb-4">the right way.</h1>
        <?php if (isset($_SESSION["User_ID"])): ?>
            <h3 class="text-white mb-4 ">Welcome back, <?= htmlspecialchars($result['name']) ?>!</h3>
            <p class="lead text-white mb-4">Explore our listings and find the perfect match for you.</p>
            <a href="listing/index.php" class="btn btn-primary btn-lg">View Listings</a>
        <?php else: ?>
            <p class="lead text-white mb-4">Join our community and start your journey with us.</p>
            <a href="auth/login.php" class="btn btn-primary btn-lg">Get Started</a>
        <?php endif; ?>
    </div>
</section>

<section>
    <div class="container mt-5">
        <div class="row row-cols-1 row-cols-md-4 g-4">
            <div class="col">
                <div class="card h-100 border-0"
                    style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);color: white;transition: transform 0.3s ease;">
                    <div class="card-body text-center" style="height: 15rem;">
                        <h5 class="card-title fw-bold" style="margin-bottom: 2rem;">
                            Sell your products
                        </h5>
                        <p class="card-text">
                            Sell anything you want, from electronics to clothing, and even cars.
                        </p>
                        <i class="fas fa-tags fa-2x" style="position: absolute; bottom: 15px; right: 15px;"></i>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 border-0"
                    style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);color: white;transition: transform 0.3s ease;">
                    <div class="card-body text-center" style="height: 15rem;">
                        <h5 class="card-title fw-bold" style="margin-bottom: 2rem;">
                            Buy anything you want
                        </h5>
                        <p class="card-text">
                            Looking for a car, a phone, or a book? At Squito, we have it all!
                        </p>
                        <i class="fas fa-shopping-cart fa-2x" style="position: absolute; bottom: 15px; right: 15px;"></i>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 border-0"
                    style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);color: white;transition: transform 0.3s ease;">
                    <div class="card-body text-center" style="height: 15rem;">
                        <h5 class="card-title fw-bold" style="margin-bottom: 2rem;">
                            Looking to track orders?
                        </h5>
                        <p class="card-text">
                            The orders section allows you to view every purchase you have ever made.
                        </p>
                        <i class="fas fa-truck fa-2x" style="position: absolute; bottom: 15px; right: 15px;"></i>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 border-0"
                    style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);color: white;transition: transform 0.3s ease;">
                    <div class="card-body text-center" style="height: 15rem;">
                        <h5 class="card-title fw-bold" style="margin-bottom: 2rem;">
                            Top notch security
                        </h5>
                        <p class="card-text">
                            We use the latest technology to keep your data safe and secure.
                        </p>
                        <i class="fas fa-shield-alt fa-2x" style="position: absolute; bottom: 15px; right: 15px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($products_results != null): ?>
<section>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Featured Products</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($products_results as $product): ?>
                <div class="col">
                    <div class="card h-100">
                        <img src='../listing/getImage.php?id=<?php echo htmlspecialchars($product['product_id']); ?>'
                            alt='<?php echo htmlspecialchars($product['title']); ?>'
                            class="img-fluid rounded-3" style="max-height: 300px; object-fit: contain;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                            <p class="card-text"><strong>Price:</strong> R<?= htmlspecialchars(number_format($product['price'], 2)) ?></p>
                            <a href="listing/product.php?id=<?= htmlspecialchars($product['product_id']) ?>" class="btn btn-primary">View Product</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>