<!-- 
What do add to landing page:
1. Create a hero section with a background image and a welcome message.
2. Second section can have the benefits of using Squito.
3. Display a few products, perhaps usig sql ... limt 3-5
4. Reviews section with user testimonials.
5. FAQ using bootstrap accordion.
6. Get started section prompting user to register.

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
    }
}


$pageTitle = "Home - Squito";

require_once __DIR__ . '/../includes/header.php';

?>

<section class="hero d-flex justify-content-center align-items-center text-center"
    style="background-image:                
                linear-gradient(to right, rgba(var(--bs-primary-rgb), 0.3), 
                rgba(var(--bs-secondary-rgb), 0.3), 
                rgba(var(--bs-primary-rgb), 0.3)), 
                url('assets/images/hero-img.jpg');
                min-height: 100vh; 
                background-size: cover; 
                background-position: center; 
                position: relative;
                margin-top: -56px;
                padding-top: 56px;
                ">
    <div class="hero-content">
        <h1 class="display-2 fw-bold text-white">Discover,</h1>
        <h1 class="display-2 fw-bold text-white">buy, or sell,</h1>
        <h1 class="display-2 fw-bold text-white mb-4">the right way.</h1>
        <?php if (isset($_SESSION["User_ID"])): ?>
            <h3 class="text-white mb-4 ">Welcome back, <?= htmlspecialchars($result['name'])?>!</h3>
            <p class="lead text-white mb-4">Explore our listings and find the perfect match for you.</p>
            <a href="listing/index.php" class="btn btn-primary btn-lg">View Listings</a>
        <?php else: ?>
            <p class="lead text-white mb-4">Join our community and start your journey with us.</p>
            <a href="auth/login.php" class="btn btn-primary btn-lg">Get Started</a>
        <?php endif; ?>
    </div>
</section >

<section>

</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>