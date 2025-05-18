<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION['Role'] != 'Admin') {
    header("Location: ../index.php");
    exit;
}

$pageTitle = "Users - Squito";

$stmt = $conn->prepare('SELECT * FROM user');
$stmt->execute();
$result = $stmt->get_result();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container">
    <h1 class="text-center">Users</h1>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered" border="1">
            <th>ID</th>
            <th>Name</th>
            <th>Surname</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
            <?php foreach ($result as $index => $row): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['surname']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                    <td><?php echo $row['isActive'] ? 'Active' : 'Inactive'; ?></td>
                    <td class="d-flex gap-2">
                        <form action="edit.php" method="POST">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['user_id']); ?>">
                            <button type="submit" name="loadEdit" class="btn btn-primary">Edit</button>
                        </form>
                        <?php if ($row['user_id'] != $_SESSION['User_ID']): ?>
                            <form action="delete.php" method="POST" id="deleteForm_<?php echo $row['user_id']; ?>">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['user_id']); ?>">
                                <button type="button" class="btn btn-danger delete-btn"
                                    data-user-id="<?php echo $row['user_id']; ?>">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
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
                Are you sure you want to delete this user? This is irreversible.
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
        let currentFormId = null;

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function () {
                currentFormId = 'deleteForm_' + this.getAttribute('data-user-id');
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