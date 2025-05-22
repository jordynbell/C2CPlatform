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

$total = 0;

$stmt = $conn->prepare('SELECT * FROM sale');
$stmt->execute();
$result = $stmt->get_result();
$sales = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();

if ($result->num_rows === 0) {
    // Set toast error messages
    $_SESSION['toast_message'] = "No sales found.";
    $_SESSION['toast_type'] = "warning";

    $conn->close();

    header("Location: ../index.php");
    exit;
}

foreach ($sales as $sale) {
    $total += (float) $sale['price'];
}

$pageTitle = "Sales Report - Squito";

$conn->close();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container">
    <h1 class="text-center">Sales</h1>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered" border="1">
            <tr>
                <th>Sale ID</th>
                <th>Product ID</th>
                <th>Date Sold</th>
                <th>Total Price</th>
            </tr>
            <?php foreach ($sales as $sale): ?>

                <tr>
                    <td><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                    <td><?php echo htmlspecialchars($sale['product_id']); ?></td>
                    <td><?php echo htmlspecialchars($sale['date_sold']); ?></td>
                    <td><?php echo htmlspecialchars("R " . $sale['price']); ?></td>
                </tr>
            <?php endforeach; ?>

            <tr>
                <td colspan="3" align="right"><strong>Total</strong></td>
                <td><strong><?php echo 'R ' . number_format($total, 2); ?></strong></td>
            </tr>

        </table>
    </div>
</div>
<?php
require_once __DIR__ . '/../../includes/footer.php';
?>