<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "Edit user - Squito";

$user_data = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    if (isset($_POST['loadEdit'])) {
        $stmt = $conn->prepare('SELECT user_id, name, surname, email, role FROM user WHERE user_id = ?');
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
        } else {
            echo "Could not load the user's information.";
            exit;
        }
    } else if (isset($_POST['saveEdit'])) {
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $stmt = $conn->prepare('UPDATE user SET name = ?, surname = ?, email = ?, role = ? WHERE user_id = ?');
        $stmt->bind_param("ssssi", $name, $surname, $email, $role, $user_id);
        if ($stmt->execute()) {
            header("Location: users.php");
            exit;
        } else {
            echo "Failed to edit user.";
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';

?>

<?php if ($user_data): ?>

    <div class="container mx-auto mt-5 mb-5" style="max-width: 60rem;">
        <h1 class="text-center mb-4">Edit User</h1>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border rounded p-4">
                    <form action="edit.php" method="post">
                        <input type="hidden" name="user_id" id="user_id" value="<?php echo $user_data["user_id"]; ?>" required>

                        <div class="mb-3">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" value="<?php echo $user_data["name"]; ?>" class="form-control" required> </br>
                        </div>
                        <div class="mb-3">
                            <label for="surname">Surname</label>
                            <input type="text" name="surname" id="surname" value="<?php echo $user_data["surname"]; ?>" class="form-control" required> </br>
                        </div>
                        <div class="mb-3">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" value="<?php echo $user_data["email"]; ?>" class="form-control" required> </br>
                        </div>
                        <div class="mb-3">
                            <label for="role">Role</label>
                            <input type="role" name="role" id="role" value="<?php echo $user_data["role"]; ?>" class="form-control" required> </br>
                        </div>
                        <div class="mb-3 d-flex justify-content-center">
                            <button type="submit" name="saveEdit" class="btn btn-primary mt-2 mb-2" style="width: 30%;">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>