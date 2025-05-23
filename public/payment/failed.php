<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    // Set toast error messages
    $_SESSION['toast_message'] = "Please log in to access this page.";
    $_SESSION['toast_type'] = "warning";

    $conn->close();
    
    // Redirect to login page
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    // Set toast error messages
    $_SESSION['toast_message'] = "Invalid order ID.";
    $_SESSION['toast_type'] = "warning";

    $conn->close();
    
    // Redirect to order page
    header("Location: ../order/index.php");
    exit;
}

// Initialise variables
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;
$reason = isset($_GET['reason']) ? htmlspecialchars($_GET['reason']) : 'payment_failed';

$pageTitle = "Payment Failed- Squito";

$conn->close();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5 mx-auto">
            <div class="card shadow border-0 rounded-lg overflow-hidden">
                <div class="card-header text-center bg-danger text-white py-3">
                    <h3 class="mb-0 fw-semibold">Payment Failed</h3>
                </div>
                <div class="card-body py-4">
                    <div class="text-center">
                        <svg width="150px" height="150px" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path
                                    d="M16 8L8 16M8.00001 8L16 16M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                    stroke="#ff0000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                </path>
                            </g>
                        </svg>
                        <p class="mb-4">We couldn't process your payment. Please try again or use a different payment
                            method.</p>
                        <p class="mb-3">You will be redirected to the order page in <span id="countdown"
                                class="fw-bold">5</span> seconds.</p>
                        <div class="mt-4">
                            <a href="../order/index.php" class="btn btn-danger px-4">Return to Order Page</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Set timer for redirection
    let counter = 5;
    const countdown = setInterval(() => {
        if (counter === 0) {
            clearInterval(countdown);
            window.location.href = '../order/index.php';
        } else {
            document.getElementById('countdown').innerText = counter;
            counter--;
        }
    }, 1000);
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>