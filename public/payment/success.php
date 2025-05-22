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

    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    // Set toast error messages
    $_SESSION['toast_message'] = "Invalid order ID.";
    $_SESSION['toast_type'] = "warning";

    $conn->close();
    
    header("Location: ../order/index.php");
    exit;
}

$order_id = $_GET['order_id'];
$customer_id = $_SESSION['User_ID'];
$stmt = $conn->prepare('SELECT * FROM `order` WHERE order_id = ? AND customer_id = ? AND status = "Paid" LIMIT 1');
$stmt->bind_param('ii', $order_id, $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    // Set toast error messages
    $_SESSION['toast_message'] = "Order not found or not paid.";
    $_SESSION['toast_type'] = "warning";

    $conn->close();

    // Order does not exist or is not paid
    header("Location: ../order/index.php");
    exit;
}

$pageTitle = "Payment Suceeded- Squito";

$conn->close();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5 mx-auto">
            <div class="card shadow border-0 rounded-lg overflow-hidden">
                <div class="card-header text-center text-white py-3" style="background-color: #1fad40;">
                    <h3 class="mb-0 fw-semibold">Payment Succeeded</h3>
                </div>
                <div class="card-body py-4">
                    <div class="text-center">
                        <svg width="150px" height="150px" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path d="M7.29417 12.9577L10.5048 16.1681L17.6729 9" stroke="#1fad40" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                <circle cx="12" cy="12" r="10" stroke="#1fad40" stroke-width="2"></circle>
                            </g>
                        </svg>
                        <p class="mb-4">Your payment processed successfully!</p>
                        <p class="mb-3">You will be redirected to the order page in <span id="countdown"
                                class="fw-bold">5</span> seconds.</p>
                        <div class="mt-4">
                            <a href="../order/index.php" class="btn px-4 text-white" style="background-color: #1fad40">Return to Order Page</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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