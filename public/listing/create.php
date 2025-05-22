<?php

require_once __DIR__ . '/../../lib/db.php';

$pageTitle = "Create Listing - Squito";

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    // Set toast error messages
    $_SESSION['toast_message'] = "You must be logged in to create a listing.";
    $_SESSION['toast_type'] = "warning";

    $conn->close();

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

    $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    $fileType = mime_content_type($_FILES['image']['tmp_name']);

    if (!in_array($fileType, $allowedMimeTypes)) {
        // Set toast error messages
        $_SESSION['toast_message'] = "Invalid image format. Only JPG, JPEG, and PNG are allowed.";
        $_SESSION['toast_type'] = "danger";
    } else if ($_FILES['image']['size'] > 2000000) { // 2MB limit
        // Set toast error messages
        $_SESSION['toast_message'] = "Image size exceeds 2MB limit.";
        $_SESSION['toast_type'] = "danger";
    } else {
        $image = file_get_contents($_FILES['image']['tmp_name']);

        $stmt = $conn->prepare("INSERT INTO product (title, description, price, category, seller_id, status, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsiss", $title, $description, $price, $category, $seller_id, $status, $image);

        if ($stmt->execute()) {
            $stmt->close();
            
            // Set toast success messages
            $_SESSION['toast_message'] = "Listing created successfully.";
            $_SESSION['toast_type'] = "success";

            $conn->close();

            header("Location: index.php");
            exit;
        } else {
            $stmt->close();

            // Set toast error messages
            $_SESSION['toast_message'] = "Failed to create listing. Please try again.";
            $_SESSION['toast_type'] = "danger";
        }
    }
}

$conn->close();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container mx-auto mt-5 mb-5" style="max-width: 60rem;">
    <h1 class="text-center mb-4">Create Listing</h1>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border rounded p-4">
                <form action="" method="post" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label for="title">Title</label>
                        <input type="text" name="title" id="title" class="form-control auto-capitalise" required>
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
                            <option value="Books">Books</option>
                            <option value="Clothing">Clothing</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Food">Food</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Health">Health</option>
                            <option value="Toys">Toys</option>
                            <option value="Vehicles">Vehicles</option>
                            <option value="Other">Other</option>
                        </select><br>
                    </div>
                    <div class="mb-3">
                        <label for="image">Image</label>
                        <input type="file" name="image" id="image" class="form-control"
                            accept="image/jpeg,image/jpg,image/png" required>
                        <small class="form-text text-muted">Upload an image for your listing (JPG, JPEG or PNG only, max
                            2MB).</small>
                    </div>
                    <div class="mb-3 d-flex justify-content-center">
                        <input type="submit" value="Create Listing" class="btn btn-primary mt-2 mb-2"
                            style="width: 40%;">
                    </div>
                </form>
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
    // Save form data as user types
    document.addEventListener('DOMContentLoaded', function () {
        // Get all form fields we want to save
        const titleField = document.getElementById('title');
        const descriptionField = document.getElementById('description');
        const priceField = document.getElementById('price');
        const categoryField = document.getElementById('category');


        // Function to save form data
        function saveFormData() {
            const formData = {
                title: titleField.value,
                description: descriptionField.value,
                price: priceField.value,
                category: categoryField.value
            };

            localStorage.setItem('createListingFormData', JSON.stringify(formData));
        }

        // Add input event listeners to all fields
        titleField.addEventListener('input', saveFormData);
        descriptionField.addEventListener('input', saveFormData);
        priceField.addEventListener('input', saveFormData);
        categoryField.addEventListener('change', saveFormData);

        // Load saved form data if it exists
        const savedData = JSON.parse(localStorage.getItem('createListingFormData'));
        if (savedData) {
            titleField.value = savedData.title || '';
            descriptionField.value = savedData.description || '';
            priceField.value = savedData.price || '';
            categoryField.value = savedData.category || '';
        }

        // Clear saved data when form is submitted
        document.querySelector('form').addEventListener('submit', function () {
            localStorage.removeItem('createListingFormData');
        });
    });
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>