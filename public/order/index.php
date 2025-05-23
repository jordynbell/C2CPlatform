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

$pageTitle = "My Orders - Squito";

$user_id = $_SESSION['User_ID'];

// Select all orders for the logged-in user.
$stmt = $conn->prepare('SELECT * FROM `order` WHERE customer_id = ?');
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container">
    <h1 class="text-center">My Orders</h1>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered" border="1">
            <tr>
                <th>Order Number</th>
                <th>Order Date</th>
                <th>Price</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo $order['order_id']; ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td><?php echo 'R ' . number_format($order['price'], 2); ?></td>
                    <td><?php echo $order['status']; ?></td>
                    <td>
                        <a href="view.php?id=<?php echo $order['product_id']; ?>&order_id=<?php echo $order['order_id']; ?>&status=<?php echo $order['status'] ?>"
                            class="btn btn-primary">View</a>
                        <?php if ($order['status'] == 'Pending payment'): ?>
                            <form id="cancelForm_<?php echo $order['order_id']; ?>" action="cancel.php" method="GET"
                                style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $order['order_id']; ?>">
                                <button type="button" class="btn btn-danger cancel-btn"
                                    data-order-id="<?php echo $order['order_id']; ?>">Cancel</button>
                            </form>
                        <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Confirm Cancellation</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to cancel this order?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmCancellation">Confirm</button>
            </div>
        </div>
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
        document.addEventListener('DOMContentLoaded', function () {
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

<script>
    // Handle cancellation confirmation with Bootstrap modal
    document.addEventListener('DOMContentLoaded', function () {
        const modal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
        const confirmButton = document.getElementById('confirmCancellation');
        let currentFormId = null;

        document.querySelectorAll('.cancel-btn').forEach(button => {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                currentFormId = 'cancelForm_' + this.getAttribute('data-order-id');
                modal.show();
            });
        });

        confirmButton.addEventListener('click', function () {
            if (currentFormId) {
                document.getElementById(currentFormId).submit();
                modal.hide();
            }
        });
    });
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>