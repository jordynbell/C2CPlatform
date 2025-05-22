<?php

session_start();

require_once __DIR__ . '/../../lib/db.php';

$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT password, isActive FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_password, $isActive);
        $stmt->fetch();
        if (password_verify($password, $db_password)) {

            if ($isActive != 1) {
                // Set toast error messages
                $_SESSION['toast_message'] = "Your account is not active. Please contact support.";
                $_SESSION['toast_type'] = "danger";

                $_SESSION['form_data'] = [
                    'email' => $email,
                ];
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }

            $_SESSION['Email'] = $email;

            $stmt = $conn->prepare("SELECT user_id, role, isActive FROM user WHERE email = ? AND isActive = 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            $_SESSION['User_ID'] = $user['user_id'];
            $_SESSION['Role'] = $user['role'];

            // Set toast success messages
            $_SESSION['toast_message'] = "Login successful!";
            $_SESSION['toast_type'] = "success";

            header("Location: ../index.php");
            exit;
        } else {
            // Set toast error messages
            $_SESSION['toast_message'] = "Invalid email or password.";
            $_SESSION['toast_type'] = "danger";

            $_SESSION['form_data'] = [
                'email' => $email,
            ];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    } else {
        // Set toast error messages
        $_SESSION['toast_message'] = "Invalid email or password.";
        $_SESSION['toast_type'] = "danger";

        $_SESSION['form_data'] = [
            'email' => $email,
        ];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

if (isset($_SESSION['form_data'])) {
    $email = htmlspecialchars($_SESSION['form_data']['email'] ?? '');
    unset($_SESSION['form_data']);
}

$conn->close();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container mx-auto mt-5 mb-5" style="max-width: 60rem;">
    <h1 class="text-center mb-4">Squito Login</h1>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border rounded p-4">
                <form action="" method="post">
                    <div class="mb-3 mt-2">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control" autocomplete="true" required
                            value="<?php echo $email; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required
                            autocomplete="off">
                    </div>
                    <div class="mb-3 d-flex justify-content-center">
                        <input type="submit" value="Login" class="btn btn-primary mt-2 mb-2" style="width: 40%;">
                    </div>

                </form>
                <div class="text-center mt-3">
                    <p class="mb-0">Don't have an account? <a href="register.php">Register here</a></p>
                </div>
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>