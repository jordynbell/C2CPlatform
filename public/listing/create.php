<?php

require_once __DIR__ . '/../../lib/db.php';

$pageTitle = "Create Listing - Squito";

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $seller_id = $_SESSION['User_ID'];
    $status = "Active";

    $stmt = $conn->prepare("INSERT INTO product (title, description, price, category, seller_id, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsis", $title, $description, $price, $category, $seller_id, $status);

    if ($stmt->execute()) {
        header("Location: ../index.php");
        $stmt->close();
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mx-auto mt-5 mb-5" style="max-width: 60rem;">
    <h1 class="text-center mb-4">Create Listing</h1>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border rounded p-4">
                <form action="" method="post">

                    <div class="mb-3">
                        <label for="title">Title</label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" class="form-control" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="price">Price</label>
                        <input type="number" step="0.01" name="price" id="price" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="category">Category</label>
                        <select name="category" id="category" class="form-control" required>
                            <option value="">Select a category</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Clothing">Clothing</option>
                            <option value="Toys">Toys</option>
                            <option value="Books">Books</option>
                            <option value="Vehicles">Vehicles</option>
                        </select><br>
                    </div>
                    <div class="mb-3 d-flex justify-content-center">
                        <input type="submit" value="Create Listing" class="btn btn-primary mt-2 mb-2" style="width: 40%;">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>