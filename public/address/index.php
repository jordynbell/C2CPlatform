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
                <th>Postal Code</th>
                <th>Country</th>
                <th>Actions</th>
            </tr>
        <tbody>
            <?php foreach ($addresses as $index => $address): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($address['address_line']); ?></td>
                    <td><?php echo htmlspecialchars($address['city']); ?></td>
                    <td><?php echo htmlspecialchars($address['province']); ?></td>
                    <td><?php echo htmlspecialchars($address['postal_code']); ?></td>
                    <td><?php echo htmlspecialchars($address['country']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $address['address_id']; ?>" class="btn btn-primary">Edit</a>
                        <a href="delete.php?id=<?php echo $address['address_id']; ?>" class="btn btn-danger">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>