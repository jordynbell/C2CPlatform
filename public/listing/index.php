<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "View Listings - Squito";

$stmt = $conn->prepare('SELECT product_id, title, description, category, price, status, image FROM product WHERE status = "Active" AND seller_id != ?');
$stmt->bind_param("i", $_SESSION['User_ID']);
$stmt->execute();
$rows = $stmt->get_result();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container">
    <h1 class="text-center mb-4">View Listings</h1>
    
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
        <?php foreach ($rows as $row): ?>
            <div class="col">
                <div class="card h-100" style="max-width: 400px;">
                    <img src="../listing/getImage.php?id=<?php echo htmlspecialchars($row['product_id']); ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($row['title']); ?>"
                         style="height: 200px; object-fit: cover;">
                    
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                        <p class="card-text" style="max-height: 100px; overflow: hidden;">
                            <?php echo htmlspecialchars($row['description']); ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($row['category']); ?></span>
                            <span class="text-primary fw-bold">R <?php echo htmlspecialchars(number_format($row['price'], 2)); ?></span>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-white border-top-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="view.php?id=<?php echo htmlspecialchars($row['product_id']); ?>&source=index" class="btn btn-outline-primary">View Details</a>
                            <a href="../order/create.php?product_id=<?php echo htmlspecialchars($row['product_id']); ?>" class="btn btn-primary">Order</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

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
require_once __DIR__ . '/../../includes/footer.php';
?>