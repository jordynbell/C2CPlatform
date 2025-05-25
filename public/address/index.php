<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    // Redirect to login page if user is not logged in
    $_SESSION['toast_message'] = "Please log in to access this page.";
    $_SESSION['toast_type'] = "warning";

    $conn->close();

    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "Addresses - Squito";

// Display addresses for the logged-in user

$stmt = $conn->prepare("SELECT * FROM address WHERE user_id = ? AND isActive = 1");
$stmt->bind_param("i", $_SESSION['User_ID']);
$stmt->execute();
$result = $stmt->get_result();
$addresses = [];
while ($row = $result->fetch_assoc()) {
    $addresses[] = $row;
}
$stmt->close();

$conn->close();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <h1 class="text-center">Addresses</h1>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered" border="1">
            <tr>
                <th>Address ID</th>
                <th>Address Line</th>
                <th>City</th>
                <th>Province</th>
                <th>Country</th>
                <th>Postal Code</th>
                <th>Actions</th>
            </tr>
            <tbody>
                <?php foreach ($addresses as $index => $address): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($address['address_line']); ?></td>
                        <td><?php echo htmlspecialchars($address['city']); ?></td>
                        <td><?php echo htmlspecialchars($address['province']); ?></td>
                        <td><?php echo htmlspecialchars($address['country']); ?></td>
                        <td><?php echo htmlspecialchars($address['postal_code']); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $address['address_id']; ?>" class="btn btn-primary">Edit</a>
                            <a href="#" class="btn btn-danger delete-btn"
                                data-address-id="<?php echo $address['address_id']; ?>">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
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
                Are you sure you want to delete this address?
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
    // Uses Bootstrap 5 for modal functionality, initialises the modal
    document.addEventListener('DOMContentLoaded', function () {
        const modal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
        const confirmButton = document.getElementById('confirmDelete');
        let deleteUrl = null;

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                deleteUrl = 'delete.php?id=' + this.getAttribute('data-address-id');
                modal.show();
            });
        });

        confirmButton.addEventListener('click', function () {
            if (deleteUrl) {
                window.location.href = deleteUrl;
                modal.hide();
            }
        });
    });
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>