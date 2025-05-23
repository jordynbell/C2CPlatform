<?php

require_once __DIR__ . '/../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (isset($_SESSION["User_ID"])) {
    // Check if the user is logged in
    $user_id = $_SESSION["User_ID"];
    $stmt = $conn->prepare("SELECT * FROM user WHERE User_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $result = $result->fetch_assoc();
    } else {
        $stmt->close();
        // Set toast error messages
        $_SESSION['toast_message'] = "User not found.";
        $_SESSION['toast_type'] = "danger";

        $conn->close();
        
        // Redirect to login page
        header("Location: auth/login.php");
        exit;
    }
    $stmt->close();
}

//Added text as arrays to reduce code duplication

$reasonTitles = [
    "Sell your products",
    "Buy anything you want",
    "Looking to track orders?",
    "Top notch security"
];

$reasonDescriptions = [
    "Sell anything you want, from electronics to clothing, and even cars.",
    "Looking for a car, a phone, or a book? At Squito, we have it all!",
    "The orders section allows you to view every purchase you have ever made.",
    "We use the latest technology to keep your data safe and secure."
];

$reasonIcons = [
    "fas fa-tags",
    "fas fa-shopping-cart",
    "fas fa-truck",
    "fas fa-shield-alt"
];

$accordionTitles = ["What is Squito?", "How do I sell my products?", "How do I buy products?", "How do I track my orders?"];
$accordionFirstSentences = [
    "Squito is a platform that allows you to buy and sell products.",
    "To sell your products, simply create an account and list your items.",
    "Interested in buying products? Look no further!",
    "You can track your orders in the orders section in the navigation bar."
];
$accordionContents = [
    "Squito is a platform that allows you to buy and sell products. We have a wide range of categories, including electronics, clothing, and more. Our platform is user-friendly and secure, making it easy for you to find what you're looking for.",
    "All you need to do is go to the listings page, and hit create listing! You can set your own prices and manage your listings easily.",
    "To buy products, browse our listings, click order on whichever item you desire, and follow the ordering process.",
    "Here, you can view your order history, and view the specifics of your order."
];

if (isset($_SESSION["User_ID"])) {
    $user_id = $_SESSION["User_ID"];
    $stmt = $conn->prepare('SELECT * FROM product WHERE status = "Active" AND seller_id != ? ORDER BY RAND() LIMIT 3');
    $stmt->bind_param("i", $user_id);
} else {
    $stmt = $conn->prepare('SELECT * FROM product WHERE status = "Active" ORDER BY RAND() LIMIT 3');
}
$stmt->execute();
$products_results = $stmt->get_result();
if ($products_results->num_rows > 0) {
    $products = $products_results->fetch_all(MYSQLI_ASSOC);
} else {
    $products = [];
}

$pageTitle = "Home - Squito";

$conn->close();

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
        <h2 class="text-center mb-4">Why Choose Squito?</h2>
        <div class="row row-cols-1 row-cols-md-4 g-4">
            <?php foreach ($reasonTitles as $index => $reasonTitle): ?>
                <div class="col">
                    <div class="card h-100 border-0"
                        style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);color: white;transition: transform 0.3s ease;">
                        <div class="card-body text-center" style="height: 15rem;">
                            <h5 class="card-title fw-bold" style="margin-bottom: 2rem;">
                                <?php echo $reasonTitle; ?>
                            </h5>
                            <p class="card-text">
                                <?php echo $reasonDescriptions[$index]; ?>
                            </p>
                            <i class="<?php echo $reasonIcons[$index] ?> fa-2x"
                                style="position: absolute; bottom: 15px; right: 15px;"></i>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($products != null): ?>
    <section>
        <div class="container mt-5">
            <h2 class="text-center mb-4">Featured Products</h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($products_results as $product): ?>
                    <div class="col">
                        <div class="card h-100">
                            <img src='listing/getImage.php?id=<?php echo htmlspecialchars($product['product_id']); ?>'
                                alt='<?php echo htmlspecialchars($product['title']); ?>' class="img-fluid rounded-3"
                                style="max-height: 300px; object-fit: contain;">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($product['title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                                <p class="card-text"><strong>Price:</strong>
                                    R<?= htmlspecialchars(number_format($product['price'], 2)) ?></p>
                                <a href="listing/view.php?id=<?= htmlspecialchars($product['product_id']) ?>"
                                    class="btn btn-primary">View Product</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<section>
    <div class="container mt-5">
        <div class="row d-flex justify-content-center">
            <div class="col-md-10 col-xl-8 text-center">
                <h3 class="mb-4">Testimonials</h3>
                <p class="mb-4 pb-2 mb-md-5 pb-md-0">
                    Don't take just our word for it. Have a look at some reviews from our awesome users!
                </p>
            </div>
        </div>

        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border">
                    <div class="card-body">
                        <h5 class="mb-3">Jack Franklin</h5>
                        <h6 class="text-primary mb-3">C# Developer</h6>
                        <p class="px-xl-3">
                            <i class="fas fa-quote-left pe-2"></i>I purchased a few second-hand programming books from
                            Squito and the overall experience was great!
                        </p>
                        <ul class="list-unstyled d-flex justify-content-center mb-0">
                            <li>
                                <i class="fas fa-star fa-sm text-warning"></i>
                            </li>
                            <li>
                                <i class="fas fa-star fa-sm text-warning"></i>
                            </li>
                            <li>
                                <i class="fas fa-star fa-sm text-warning"></i>
                            </li>
                            <li>
                                <i class="fas fa-star fa-sm text-warning"></i>
                            </li>
                            <li>
                                <i class="fas fa-star-half-alt fa-sm text-warning"></i>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border">
                    <div class="card-body">
                        <h5 class="mb-3">Efit Lis</h5>
                        <h6 class="text-primary mb-3">Graphic Designer</h6>
                        <p class="px-xl-3">
                            <i class="fas fa-quote-left pe-2"></i>I sold my old laptop on Squito and the process was
                            extremly easy. The UI is quite intuitive and user-friendly.
                        </p>
                        <ul class="list-unstyled d-flex justify-content-center mb-0">
                            <li>
                                <i class="fas fa-star fa-sm text-warning"></i>
                            </li>
                            <li>
                                <i class="fas fa-star fa-sm text-warning"></i>
                            </li>
                            <li>
                                <i class="fas fa-star fa-sm text-warning"></i>
                            </li>
                            <li>
                                <i class="fas fa-star fa-sm text-warning"></i>
                            </li>
                            <li>
                                <i class="fas fa-star fa-sm text-warning"></i>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border">
                    <div class="card-body">
                        <h5 class="mb-3">Anonymous</h5>
                        <h6 class="text-primary mb-3">[Redacted]</h6>
                        <p class="px-xl-3">
                            <i class="fas fa-quote-left pe-2"></i>I purchased an old car and it was in great condition.
                            I also sell some of my old clothes on Squito. I love the platform!
                        </p>
                        <ul class="list-unstyled d-flex justify-content-center mb-0">
                            <li>
                                <i class="fas fa-star fa-sm text-warning"></i>
                            </li>
                            <li>
                                <i class="fas fa-star fa-sm text-warning"></i>
                            </li>
                            <li>
                                <i class="fas fa-star fa-sm text-warning"></i>
                            </li>
                            <li>
                                <i class="fas fa-star fa-sm text-warning"></i>
                            </li>
                            <li>
                                <i class="far fa-star fa-sm text-warning"></i>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section>
    <div class="container mt-5">
        <div class="row d-flex justify-content-center">
            <div class="col-md-10 col-xl-8 text-center">
                <h3 class="mb-4">Frequently Asked Questions</h3>
                <p class="mb-4 pb-2 mb-md-5 pb-md-0">
                    Have a question? We have the answer! If you have any other questions, feel free to contact us.
                </p>
            </div>
        </div>
        <div class="accordion" id="accordionFAQ">
            <?php foreach ($accordionTitles as $index => $accordionTitle): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?>" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>"
                            aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $index ?>">
                            <?php echo $accordionTitle; ?>
                        </button>
                    </h2>
                    <div id="collapse<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>"
                        data-bs-parent="#accordionFAQ">
                        <div class="accordion-body">
                            <strong><?php echo $accordionFirstSentences[$index] ?></strong>
                            <?php echo $accordionContents[$index]; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
</section>

<?php if (!isset($_SESSION["User_ID"])): ?>
    
<section>
    <div class="container mt-5">
        <div class="row d-flex justify-content-center">
            <div class="col-md-10 col-xl-8 text-center">
                <h3 class="mb-4">Get Started with Squito</h3>
                <p class="mb-4 pb-2 mb-md-5 pb-md-0">
                    Join our community and start your journey with us. Sign up today!
                </p>
                <a href="auth/register.php" class="btn btn-primary btn-lg">Register Now</a>
            </div>
        </div>
    </div>
</section>

<?php endif; ?>

<section>
    <div class="container mt-5">
        <div class="row d-flex justify-content-center">
            <div class="col-md-10 col-xl-8 text-center">
                <h3 class="mb-4">Contact Us</h3>
                <p class="mb-4">
                    Have questions or need support? We're here to help!
                </p>
                <div class="card p-4 shadow-sm">
                    <div class="text-center mb-4">
                        <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                        <h5>Email Us</h5>
                        <p>For questions, support, or feedback:</p>
                        <a href="mailto:support@squito.com" class="btn btn-outline-primary">
                            support@squito.com
                        </a>
                    </div>
                    <div class="text-center">
                        <p class="mb-0"><small>We typically respond within 24-48 hours</small></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="toast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Notification</strong>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>

<script>
    // Display toast message if it exists in session
    <?php if (isset($_SESSION['toast_message'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');

            // Set message and style
            toastMessage.textContent = "<?php echo $_SESSION['toast_message']; ?>";
            toast.classList.add('text-bg-<?php echo $_SESSION['toast_type'] ?? 'primary'; ?>');

            // Initialize and show toast
            const bsToast = new bootstrap.Toast(toast, {
                autohide: true,
                delay: 3500
            });
            bsToast.show();

            // Clear session variables
            <?php
            unset($_SESSION['toast_message']);
            unset($_SESSION['toast_type']);
            ?>
        });
    <?php endif; ?>
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>