<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "Addresses - Squito";

$stmt = $conn->prepare("SELECT * FROM address WHERE user_id = ? AND isActive = 1");
$stmt->bind_param("i", $_SESSION['User_ID']);
$stmt->execute();
$result = $stmt->get_result();
$addresses = [];
while ($row = $result->fetch_assoc()) {
    $addresses[] = $row;
}
$stmt->close();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <h1 class="text-center">Addresses</h1>
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

<script>
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