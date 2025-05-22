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

$pageTitle = "Edit Listing - Squito";

// Initalise the product data variable before loading the form
$product_data = null;


if (!isset($_GET['id'])) {
    // Set toast error messages
    $_SESSION['toast_message'] = "No product ID provided.";
    $_SESSION['toast_type'] = "danger";

    $conn->close();

    // If no ID provided, redirect to seller's listings
    header("Location: seller_index.php");
    exit;
}

$product_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * from product where product_id = ? AND seller_id = ? AND status = 'active'");
$stmt->bind_param("ii", $product_id, $_SESSION['User_ID']);
$stmt->execute();
$product_result = $stmt->get_result();

if ($product_result->num_rows > 0) {
    $product_data = $product_result->fetch_assoc();
    $stmt->close();
} else {
    // Set toast error messages
    $_SESSION['toast_message'] = "No product found or you do not have permission to edit this product.";
    $_SESSION['toast_type'] = "danger";

    $stmt->close();

    $conn->close();

    header("Location: seller_index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $product_id = $_POST['product_id'];

    // Check if a new image was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($fileType, $allowedMimeTypes)) {
            // Set toast error messages
            $_SESSION['toast_message'] = "Only JPG, JPEG and PNG images are allowed.";
            $_SESSION['toast_type'] = "danger";
        } else if ($_FILES['image']['size'] > 2000000) { // 2MB limit
            // Set toast error messages
            $_SESSION['toast_message'] = "Image size must be less than 2MB.";
            $_SESSION['toast_type'] = "danger";
        } else {
            $image = file_get_contents($_FILES['image']['tmp_name']);

            // Update with new image
            $stmt = $conn->prepare("UPDATE product SET title = ?, description = ?, price = ?, category = ?, image = ? WHERE product_id = ? and status = 'active'");
            if (!$stmt) {
                // Set toast error messages
                $_SESSION['toast_message'] = "Error preparing statement: " . $conn->error;
                $_SESSION['toast_type'] = "danger";
                exit;
            }
            $stmt->bind_param("ssdssi", $title, $description, $price, $category, $image, $product_id);
        }
    } else {
        // Update without new image if no new image is uploaded by the seller.
        $stmt = $conn->prepare("UPDATE product SET title = ?, description = ?, price = ?, category = ? WHERE product_id = ? and status = 'active'");
        if (!$stmt) {
            // Set toast error messages
            $_SESSION['toast_message'] = "Error preparing statement: " . $conn->error;
            $_SESSION['toast_type'] = "danger";
            exit;
        }
        $stmt->bind_param("ssdsi", $title, $description, $price, $category, $product_id);
    }

    // Only execute if there are no errors.
    if (isset($stmt)) {
        if ($stmt->execute()) {
            $stmt->close();

            // Set toast success messages
            $_SESSION['toast_message'] = "Product updated successfully.";
            $_SESSION['toast_type'] = "success";

            $conn->close();

            header("Location: seller_index.php");
            exit;
        } else {
            $stmt->close();
            
            // Set toast error messages
            $_SESSION['toast_message'] = "Error updating product: " . $stmt->error;
            $_SESSION['toast_type'] = "danger";

            $conn->close();

            header("Location: seller_index.php");
            exit;
        }
    }
}

$conn->close();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container mx-auto mt-5 mb-5" style="max-width: 60rem;">
    <h1 class="text-center mb-4">Edit Listing</h1>
    <?php if ($product_data): ?>
        <div class="row justify-content-center mb-4 mt-4">
            <div class="col-md-6">
                <div class="card shadow-sm border rounded p-4">
                    <form action="" method="post" enctype="multipart/form-data">
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
                                <option value="Books" <?php echo ($product_data["category"] == "Books") ? "selected" : ""; ?>>
                                    Books</option>
                                <option value="Clothing" <?php echo ($product_data["category"] == "Clothing") ? "selected" : ""; ?>>Clothing</option>
                                <option value="Electronics" <?php echo ($product_data["category"] == "Electronics") ? "selected" : ""; ?>>Electronics</option>
                                <option value="Food" <?php echo ($product_data["category"] == "Food") ? "selected" : ""; ?>>
                                    Food</option>
                                <option value="Furniture" <?php echo ($product_data["category"] == "Furniture") ? "selected" : ""; ?>>Furniture</option>
                                <option value="Health" <?php echo ($product_data["category"] == "Health") ? "selected" : ""; ?>>Health</option>
                                <option value="Toys" <?php echo ($product_data["category"] == "Toys") ? "selected" : ""; ?>>
                                    Toys</option>
                                <option value="Vehicles" <?php echo ($product_data["category"] == "Vehicles") ? "selected" : ""; ?>>Vehicles</option>
                                <option value="Other" <?php echo ($product_data["category"] == "Other") ? "selected" : ""; ?>>
                                    Other</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="image">Product Image</label>
                            <?php if (!empty($product_data["image"])): ?>
                                <div class="mb-3 text-center">
                                    <p>Current Image</p>
                                    <img src="../listing/getImage.php?id=<?php echo $product_data["product_id"]; ?>"
                                        alt="Product Image" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image" id="image" class="form-control"
                                accept="image/jpeg,image/jpg,image/png">
                            <small class="form-text text-muted">Upload an image for your listing (JPG, JPEG or PNG only, max
                                2MB).</small>
                        </div>
                        <div class="mb-3 d-flex justify-content-center">
                            <button type="button" value="Edit Listing" class="form-control btn btn-primary"
                                style="width: 40%;" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                                Edit Listing</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Confirm Edit</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to edit this listing?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmEdit">Confirm</button>
            </div>
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
        document.addEventListener('DOMContentLoaded', function () {
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
    document.addEventListener('DOMContentLoaded', function () {
        const editForm = document.querySelector('form[action=""][method="post"]');
        const editButton = document.querySelector('[data-bs-target="#staticBackdrop"]');
        const confirmButton = document.getElementById('confirmEdit');
        const modal = new bootstrap.Modal(document.getElementById('staticBackdrop'));

        editButton.removeAttribute('data-bs-toggle');
        editButton.removeAttribute('data-bs-target');

        editButton.addEventListener('click', function (e) {
            e.preventDefault();

            if (editForm.reportValidity()) {
                modal.show();
            }
        });

        confirmButton.addEventListener('click', function () {
            modal.hide();
            editForm.submit();
        });
    });
</script>

<?php
$conn->close();
require_once __DIR__ . '/../../includes/footer.php';
?>