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

if ($_SESSION['Role'] != 'Admin') {
    // Set toast error messages
    $_SESSION['toast_message'] = "You do not have permission to access this page.";
    $_SESSION['toast_type'] = "warning";

    $conn->close();

    header("Location: ../index.php");
    exit;
}

$pageTitle = "Edit user - Squito";

$user_data = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    if (isset($_POST['loadEdit'])) {
        $stmt = $conn->prepare('SELECT user_id, name, surname, email, role, isActive FROM user WHERE user_id = ?');
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $stmt->close();

        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
        } else {
            // Set toast error messages
            $_SESSION['toast_message'] = "User not found.";
            $_SESSION['toast_type'] = "warning";

            $conn->close();

            header("Location: index.php");
            exit;
        }
    } else if (isset($_POST['saveEdit'])) {
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $email = $_POST['email'];
        $role = $_POST['role'];

        if ($user_id == $_SESSION["User_ID"]) {
            $stmt = $conn->prepare('UPDATE user SET name = ?, surname = ?, email = ?, role = ? WHERE user_id = ?');
            $stmt->bind_param("ssssi", $name, $surname, $email, $role, $user_id);
        } else {
            $isActive = $_POST['isActive'];
            $stmt = $conn->prepare('UPDATE user SET name = ?, surname = ?, email = ?, role = ?, isActive = ? WHERE user_id = ?');
            $stmt->bind_param("ssssii", $name, $surname, $email, $role, $isActive, $user_id);
        }

        if ($stmt->execute()) {
            $stmt->close();

            // Set toast success messages
            $_SESSION['toast_message'] = "User edited successfully.";
            $_SESSION['toast_type'] = "success";

            $conn->close();

            header("Location: index.php");
            exit;
        } else {
            $stmt->close();

            // Set toast error messages
            $_SESSION['toast_message'] = "Error editing user.";
            $_SESSION['toast_type'] = "danger";

            $conn->close();

            header("Location: index.php");
            exit;
        }
    }
}

$conn->close();

require_once __DIR__ . '/../../includes/header.php';

?>

<?php if ($user_data): ?>

    <div class="container mx-auto mt-5 mb-5" style="max-width: 60rem;">
        <h1 class="text-center mb-4">Edit User</h1>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border rounded p-4">
                    <form action="edit.php" method="post">
                        <input type="hidden" name="user_id" id="user_id" value="<?php echo $user_data["user_id"]; ?>"
                            required>
                        <input type="hidden" name="saveEdit">

                        <div class="mb-3">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" value="<?php echo $user_data["name"]; ?>"
                                class="form-control auto-capitalise" required> </br>
                        </div>
                        <div class="mb-3">
                            <label for="surname">Surname</label>
                            <input type="text" name="surname" id="surname" value="<?php echo $user_data["surname"]; ?>"
                                class="form-control auto-capitalise" required> </br>
                        </div>
                        <div class="mb-3">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" value="<?php echo $user_data["email"]; ?>"
                                class="form-control" required> </br>
                        </div>
                        <div class="mb-3">
                            <label for="role">Role</label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="Admin" <?php echo ($user_data["role"] == "Admin") ? "selected" : ""; ?>>Admin
                                </option>
                                <option value="Normal" <?php echo ($user_data["role"] == "Normal") ? "selected" : ""; ?>>
                                    Normal</option>
                            </select>
                        </div>
                        <?php if ($user_data["user_id"] != $_SESSION["User_ID"]): ?>
                            <div class="mb-3">
                                <label for="isActive">Status</label>
                                <select name="isActive" id="isActive" class="form-control" required>
                                    <option value="1" <?php echo ($user_data["isActive"] == "1") ? "selected" : ""; ?>>Active
                                    </option>
                                    <option value="0" <?php echo ($user_data["isActive"] == "0") ? "selected" : ""; ?>>
                                        InActive</option>
                                </select>
                            </div>
                        <?php endif; ?>
                        <div class="mb-3 d-flex justify-content-center">
                            <button type="button" name="saveEdit" class="btn btn-primary mt-2 mb-2" style="width: 30%;"
                                data-bs-toggle="modal" data-bs-target="#staticBackdrop">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Confirm Edit</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to edit this user?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmEdit">Confirm</button>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const capitalizeInputs = document.querySelectorAll('.auto-capitalise');
        const editForm = document.querySelector('form[action="edit.php"][method="post"]');
        const editButton = document.querySelector('[data-bs-target="#staticBackdrop"]');
        const confirmButton = document.getElementById('confirmEdit');
        const modal = new bootstrap.Modal(document.getElementById('staticBackdrop'));

        editButton.removeAttribute('data-bs-toggle');
        editButton.removeAttribute('data-bs-target');

        capitalizeInputs.forEach(input => {
            if (input.value.length > 0) {
                input.value = input.value.charAt(0).toUpperCase() + input.value.slice(1);
            }

            input.addEventListener('input', function (e) {
                let value = e.target.value;
                if (value.length > 0) {
                    e.target.value = value.charAt(0).toUpperCase() + value.slice(1);
                }
            });

            input.addEventListener('blur', function (e) {
                let value = e.target.value.trim();
                if (value.length > 0) {
                    e.target.value = value.split(' ')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                        .join(' ');
                }
            });
        });

        editButton.addEventListener('click', function (e) {
            e.preventDefault();

            if (editForm.reportValidity()) {
                modal.show();
            }
        });

        confirmButton.addEventListener('click', function () {
            modal.hide();
            editForm.submit();
        });
    });
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>