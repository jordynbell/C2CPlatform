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

$pageTitle = "Seller Listings - Squito";

// Fetch seller listings from the database
$seller_id = $_SESSION["User_ID"];
$stmt = $conn->prepare('SELECT product_id, title, description, category, price, status, image FROM product WHERE seller_id = ?');
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$conn->close();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container">
    <h1 class="text-center">Seller Listings</h1>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered" border="1">
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Category</th>
                <th>Price</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php foreach ($result as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo 'R ' . htmlspecialchars(number_format($row['price'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <?php if ($row['status'] == 'Sold' || $row['status'] == 'Deleted'): ?>
                        <td><a href="view.php?id=<?php echo htmlspecialchars($row['product_id']); ?>&source=seller"
                                class="btn btn-primary">View</a></td>
                    <?php else: ?>
                        <td>
                            <a href="view.php?id=<?php echo htmlspecialchars($row['product_id']); ?>&source=seller"
                                class="btn btn-primary">View</a>
                            <a href="edit.php?id=<?php echo htmlspecialchars($row['product_id']); ?>"
                                class="btn btn-warning text-white">Edit</a>

                            <form action="delete.php" method="POST" style="display:inline-block; margin-right:5px;"
                                id="deleteForm_<?php echo $row['product_id']; ?>">
                                <input type="hidden" name="product_id"
                                    value="<?php echo htmlspecialchars($row['product_id']); ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="button" class="btn btn-danger delete-btn"
                                    data-product-id="<?php echo $row['product_id']; ?>">Delete</button>
                            </form>
                        </td>
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
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Confirm Deletion</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this listing?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Confirm</button>
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
    document.addEventListener('DOMContentLoaded', function () {
        const modal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
        const confirmButton = document.getElementById('confirmDelete');
        let currentFormId = null;

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function () {
                currentFormId = 'deleteForm_' + this.getAttribute('data-product-id');
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