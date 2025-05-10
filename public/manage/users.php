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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST["user_id"];
    $stmt = $conn->prepare('DELETE FROM user WHERE user_id = ?');
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

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
            <th>Actions</th>

            <?php
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["user_id"] . "</td>";
                echo "<td>" . $row["name"] . "</td>";
                echo "<td>" . $row["surname"] . "</td>";
                echo "<td>" . $row["email"] . "</td>";
                echo "<td>" . $row["role"] . "</td>";
                echo "<td class='d-flex gap-2'>";
                if ($user_id != $row["user_id"]) {
                    echo "<form action='' method='POST'><input type='hidden' name='user_id' value='" . htmlspecialchars($row['user_id']) . "'><button type='submit' class='btn btn-danger'>Delete</button></form>";
                }
                else
                {
                    echo "<div style='width: 72px;'></div>";
                }
                echo "<form action='edit.php' method='POST'><input type='hidden' name='user_id' value='" . htmlspecialchars($row['user_id']) . "'><button type='submit' name='loadEdit' class='btn btn-primary'>Edit</button></form>";
                echo "</td>";
                echo "</tr>";
            }
            $stmt->close();
            ?>

        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>