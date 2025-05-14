<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "Edit Listing - Squito";

$sql = "SELECT product_id, title FROM product WHERE seller_id = '{$_SESSION['User_ID']}' AND status = 'active'";
$result = $conn->query($sql);

// Initalise the product data variable before loading the form
$product_data = null;

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * from product where product_id = ? AND seller_id = ? AND status = 'active'");
    $stmt->bind_param("ii", $product_id, $_SESSION['User_ID']);
    $stmt->execute();
    $product_result = $stmt->get_result();

    if ($product_result->num_rows > 0) {
        $product_data = $product_result->fetch_assoc();
    } else {
        echo "No product found or you do not have permission to edit this product.";
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $product_id = $_POST['product_id'];
    $seller_id = $_SESSION['User_ID'];

    $stmt = $conn->prepare("UPDATE product SET title = ?, description = ?, price = ?, category = ?, seller_id = ? WHERE product_id = ? and status = 'active'");
    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error;
        exit;
    }
    $stmt->bind_param("ssdsii", $title, $description, $price, $category, $seller_id, $product_id);

    if ($stmt->execute()) {
        header("Location: seller_index.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container mx-auto mt-5 mb-5" style="max-width: 60rem;">
    <h1 class="text-center mb-4">Edit Listing</h1>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border rounded p-4">
                <form action="" method="get">
                    <div class="form-group">
                        <div class="mb-3">
                            <label for="id">Product to edit</label>
                            <select name="id" id="id" class="form-control" required>
                                <option value="">Select a product</option>
                                <?php

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        // Check if the product is already selected
                                        $selected = (isset($_GET['id']) && $_GET['id'] == $row["product_id"]) ? "selected" : "";
                                        echo "<option value='{$row["product_id"]}' $selected>{$row["title"]}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3 d-flex justify-content-center">
                            <button type="submit" class="form-control btn btn-primary" style="width: 40%;">Load
                                Product</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php if ($product_data): ?>
    <div class="container mx-auto mt-5 mb-5" style="max-width: 60rem;">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border rounded p-4">
                    <form action="" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $product_data["product_id"]; ?>">

                        <div class="mb-3">
                            <label for="title">Title</label>
                            <input type="text" name="title" id="title" value="<?php echo $product_data["title"]; ?>"
                                class="form-control auto-capitalise" required>
                        </div>

                        <div class="mb-3">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control"
                                required><?php echo $product_data["description"]; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price">Price</label>
                            <input type="number" step="0.01" name="price" id="price"
                                value="<?php echo $product_data["price"]; ?>" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="category">Category</label>
                            <select name="category" id="category" class="form-control" required>
                                <option value="">Select a category</option>
                                <option value="Electronics" <?php echo ($product_data["category"] == "Electronics") ? "selected" : ""; ?>>Electronics</option>
                                <option value="Furniture" <?php echo ($product_data["category"] == "Furniture") ? "selected" : ""; ?>>Furniture</option>
                                <option value="Clothing" <?php echo ($product_data["category"] == "Clothing") ? "selected" : ""; ?>>Clothing</option>
                                <option value="Toys" <?php echo ($product_data["category"] == "Toys") ? "selected" : ""; ?>>
                                    Toys</option>
                                <option value="Books" <?php echo ($product_data["category"] == "Books") ? "selected" : ""; ?>>
                                    Books</option>
                                <option value="Vehicles" <?php echo ($product_data["category"] == "Vehicles") ? "selected" : ""; ?>>Vehicles</option>
                            </select>
                        </div>
                        <div class="mb-3 d-flex justify-content-center">
                            <input type="submit" value="Edit Listing" class="form-control btn btn-primary"
                                style="width: 40%;">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
$conn->close();
require_once __DIR__ . '/../../includes/footer.php';
?>