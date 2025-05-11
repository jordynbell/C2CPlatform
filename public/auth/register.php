<?php

session_start();
require_once __DIR__ . '/../../lib/db.php';

$incorrectPassword = false;
$errorMessage = '';
$name = '';
$surname = '';
$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = "Normal";

    if (strlen($password) < 8) {
        $_SESSION['flash_error'] = 'pwd_length';
        $_SESSION['form_data'] = [
            'name' => $name,
            'surname' => $surname,
            'email' => $email
        ];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else if ($password !== $confirm_password) {
        $_SESSION['flash_error'] = 'pwd_mismatch';
        $_SESSION['form_data'] = [
            'name' => $name,
            'surname' => $surname,
            'email' => $email
        ];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO user (name, surname, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $surname, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}

if (isset($_SESSION['flash_error'])) {
    $incorrectPassword = true;

    if ($_SESSION['flash_error'] === 'pwd_length') {
        $errorMessage = "Password must be at least 8 characters long.";
    } else if ($_SESSION['flash_error'] === 'pwd_mismatch') {
        $errorMessage = "Passwords do not match.";
    }

    unset($_SESSION['flash_error']);
}

if (isset($_SESSION['form_data'])) {
    $name = htmlspecialchars($_SESSION['form_data']['name'] ?? '');
    $surname = htmlspecialchars($_SESSION['form_data']['surname'] ?? '');
    $email = htmlspecialchars($_SESSION['form_data']['email'] ?? '');

    unset($_SESSION['form_data']);
}

$conn->close();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container mx-auto mt-5 mb-5" style="max-width: 60rem;">
    <h1 class="text-center mb-4">Squito Registration</h1>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border rounded p-4">
                <form action="" method="post">
                    <div class="mb-3 mt-2">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control" required
                            value="<?php echo $name; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="surname">Surname</label>
                        <input type="text" name="surname" id="surname" class="form-control" required
                            value="<?php echo $surname; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required
                            value="<?php echo $email; ?>">
                        <small class="form-text text-muted">We'll never share your email with anyone else.</small>
                    </div>
                    <div class="mb-3">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required
                            autocomplete="off">
                        <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password">Re-enter Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                            required autocomplete="off">
                    </div>
                    <div class="mb-3 d-flex justify-content-center">
                        <input type="submit" value="Register" class="btn btn-primary mt-2 mb-2" style="width: 40%;">
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center mt-3">
            <p class="mb-0">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <?php echo $errorMessage; ?>
            </div>
        </div>
    </div>

    <script>
        <?php if ($incorrectPassword): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var toastElement = document.getElementById('errorToast');
                var toast = new bootstrap.Toast(toastElement, {
                    autohide: true,
                    delay: 3500,
                    animation: true
                });
                toast.show();
            });
        <?php endif; ?>
    </script>
    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>