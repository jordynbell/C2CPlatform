<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "Seller Listings - Squito";

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container">
    <h1 class="text-center">Seller Listings</h1>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered" border="1">
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Category</th>
                <th>Price</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php

            $seller_id = $_SESSION["User_ID"];
            $stmt = $conn->prepare('SELECT product_id, title, description, category, price, status FROM product WHERE seller_id = ?');
            $stmt->bind_param("i", $seller_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                echo "<td>R " . htmlspecialchars($row['price']) . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                if ($row['status'] == 'Sold' || $row['status'] == 'Deleted') {
                    echo "<td></td>";
                } else {
                    echo "<td>
                        <form action='delete.php' method='POST' style='display:inline-block; margin-right:5px;' id='deleteForm_" . $row['product_id'] . "'>
                            <input type='hidden' name='product_id' value='" . htmlspecialchars($row['product_id']) . "'>
                            <input type='hidden' name='action' value='delete'>
                            <button type='button' class='btn btn-danger delete-btn' data-product-id='" . $row['product_id'] . "'>Delete</button>
                        </form>
                        <form action='edit.php' method='POST' style='display:inline-block;'>
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

<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Confirm Deletion</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this listing?
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
                currentFormId = 'deleteForm_' + this.getAttribute('data-product-id');
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