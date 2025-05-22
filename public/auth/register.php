<?php

session_start();
require_once __DIR__ . '/../../lib/db.php';

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
        // Set toast error messages
        $_SESSION['toast_message'] = "Password must be at least 8 characters long.";
        $_SESSION['toast_type'] = "danger";

        $_SESSION['form_data'] = [
            'name' => $name,
            'surname' => $surname,
            'email' => $email
        ];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else if ($password !== $confirm_password) {
        // Set toast error messages
        $_SESSION['toast_message'] = "Passwords do not match.";
        $_SESSION['toast_type'] = "danger";

        $_SESSION['form_data'] = [
            'name' => $name,
            'surname' => $surname,
            'email' => $email
        ];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            // Set toast error messages
            $_SESSION['toast_message'] = "Email already exists. Please use a different email.";
            $_SESSION['toast_type'] = "danger";

            $_SESSION['form_data'] = [
                'name' => $name,
                'surname' => $surname,
                'email' => $email
            ];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO user (name, surname, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $surname, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            // Set toast success messages
            $_SESSION['toast_message'] = "Registration successful! You can now log in.";
            $_SESSION['toast_type'] = "success";

            header("Location: login.php");
            exit;
        } else {
            // Set toast error messages
            $_SESSION['toast_message'] = "An error occurred during registration. Please try again.";
            $_SESSION['toast_type'] = "danger";

            echo "Error: " . $stmt->error;
        }
    }
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
                        <input type="text" name="name" id="name" class="form-control auto-capitalise" autocomplete="true" required
                            value="<?php echo $name; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="surname">Surname</label>
                        <input type="text" name="surname" id="surname" class="form-control auto-capitalise" autocomplete="true" required
                            value="<?php echo $surname; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control" autocomplete="true" required
                            value="<?php echo $email; ?>">
                        <small class="form-text text-muted">We'll never share your email with anyone else.</small>
                    </div>
                    <div class="mb-3">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control"
                            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                            title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters"
                            required autocomplete="off">
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
    document.addEventListener('DOMContentLoaded', function() {
        const capitalizeInputs = document.querySelectorAll('.auto-capitalise');

        capitalizeInputs.forEach(input => {
            if (input.value.length > 0) {
                input.value = input.value.charAt(0).toUpperCase() + input.value.slice(1);
            }

            input.addEventListener('input', function(e) {
                let value = e.target.value;
                if (value.length > 0) {
                    e.target.value = value.charAt(0).toUpperCase() + value.slice(1);
                }
            });

            input.addEventListener('blur', function(e) {
                let value = e.target.value.trim();
                if (value.length > 0) {
                    e.target.value = value.split(' ')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                        .join(' ');
                }
            });
        });
    });
</script>
<?php
require_once __DIR__ . '/../../includes/footer.php';
?>