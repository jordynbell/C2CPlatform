<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "My Orders - Squito";

$user_id = $_SESSION['User_ID'];

$stmt = $conn->prepare('SELECT * FROM `order` WHERE customer_id = ?');
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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
                            <form id="cancelForm_<?php echo $order['order_id']; ?>" action="cancel.php" method="GET" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $order['order_id']; ?>">
                                <button type="button" class="btn btn-danger cancel-btn" data-order-id="<?php echo $order['order_id']; ?>">Cancel</button>
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

<script>
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