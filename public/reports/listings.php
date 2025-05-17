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

$pageTitle = "All Listings - Squito";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $seller_id = $_SESSION['User_ID'];

    if (isset(($_POST['action'])))
    {
        if ($_POST['action'] == 'delete')
        {
            $stmt = $conn->prepare('UPDATE product SET status = "Deleted" WHERE product_id = ? AND seller_id = ?');
            $stmt->bind_param("ii", $product_id, $seller_id);
            if (!$stmt->execute()) {
                echo "Error: " / $stmt->error;
            }
        }
        elseif ($_POST['action'] == 'edit')
        {
            header("Location: ../listing/edit.php?id=" . $product_id);
            exit;
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container">
    <h1 class="text-center">All Listings</h1>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered" border="1">
            <tr>
                <th>Product ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Category</th>
                <th>Price</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php

            $seller_id = $_SESSION["User_ID"];
            $stmt = $conn->prepare('SELECT product_id, title, description, category, price, status FROM product');
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['product_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                echo "<td>R " . htmlspecialchars(number_format($row['price'],2)) . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                if ($row['status'] == 'Sold' || $row['status'] == 'Deleted') {
                    echo "<td></td>";
                } else {
                    echo "<td>
                        <form action='' method='POST' style='display:inline-block; margin-right:5px;'>
                            <input type='hidden' name='product_id' value='" . htmlspecialchars($row['product_id']) . "'>
                            <input type='hidden' name='action' value='delete'>
                            <button type='submit' class='btn btn-danger'>Delete</button>
                        </form>
                        <form action='' method='POST' style='display:inline-block;'>
                            <input type='hidden' name='product_id' value='" . htmlspecialchars($row['product_id']) . "'>
                            <input type='hidden' name='action' value='edit'>
                            <button type='submit' class='btn btn-primary'>Edit</button>
                        </form>
                    </td>";
                }
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>