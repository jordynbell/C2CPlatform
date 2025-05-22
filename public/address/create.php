<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "Create Address - Squito";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address_line = $_POST["address_line"];
    $city = $_POST["city"];
    $province = $_POST["province"];
    $country = $_POST["country"];
    $postal_code = $_POST["postal_code"];
    $user_id = $_SESSION["User_ID"];

    $stmt = $conn->prepare("INSERT INTO address (user_id, address_line, city, province, country, postal_code) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $address_line, $city, $province, $country, $postal_code);

    if ($stmt->execute()) {
        // Set toast success messages
        $_SESSION['toast_message'] = "Address created successfully!";
        $_SESSION['toast_type'] = "success";

        header("Location: index.php");
        exit;
    } else {
        // Set toast error messages
        $_SESSION['toast_message'] = "Failed to create address. Please try again.";
        $_SESSION['toast_type'] = "danger";

        header("Location: create.php");
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container mx-auto mt-5 mb-5" style="max-width: 60rem;">
    <h1 class="text-center mb-4">Create Address</h1>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border rounded p-4">
                <form action="" method="post">
                    <div class="mb-3 mt-2">
                        <label for="address_line" class="form-label">Address Line:</label>
                        <input type="text" class="form-control auto-capitalise" name="address_line" id="address_line"
                            placeholder="123 Steyn Road, Grape Village" required>
                    </div>
                    <div class="mb-3">
                        <label for="city" class="form-label">City:</label>
                        <input type="text" class="form-control auto-capitalise" name="city" id="city"
                            placeholder="Cape Town" required>
                    </div>
                    <div class="mb-3">
                        <label for="province" class="form-label">Province:</label>
                        <input type="text" class="form-control auto-capitalise" name="province" id="province"
                            placeholder="Western Cape" required>
                    </div>
                    <div class="mb-3">
                        <label for="country" class="form-label">Country:</label>
                        <input type="text" class="form-control auto-capitalise" name="country" id="country"
                            placeholder="South Africa" required>
                    </div>
                    <div class="mb-3">
                        <label for="postal_code" class="form-label">Postal Code:</label>
                        <input type="text" class="form-control" name="postal_code" id="postal_code" placeholder="4321" pattern="[0-9]{4,5}" maxlength="5" title="Enter digits only." required>
                    </div>
                    <div class="mb-3 text-center">
                        <button type="submit" class="btn btn-primary">Create Address</button>
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
        document.addEventListener('DOMContentLoaded', function() {
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
    document.addEventListener('DOMContentLoaded', function() {
        // Get all form fields we want to save
        const addressLineField = document.getElementById('address_line');
        const cityField = document.getElementById('city');
        const provinceField = document.getElementById('province');
        const countryField = document.getElementById('country');
        const postalCodeField = document.getElementById('postal_code')


        // Function to save form data
        function saveFormData() {
            const formData = {
                address_line: addressLineField.value,
                city: cityField.value,
                province: provinceField.value,
                country: countryField.value,
                postal_code: postalCodeField.value
            };

            localStorage.setItem('createAddressFormData', JSON.stringify(formData));
        }

        // Add input event listeners to all fields
        addressLineField.addEventListener('input', saveFormData);
        cityField.addEventListener('input', saveFormData);
        provinceField.addEventListener('input', saveFormData);
        countryField.addEventListener('input', saveFormData);
        postalCodeField.addEventListener('input', saveFormData);

        // Load saved form data if it exists
        const savedData = JSON.parse(localStorage.getItem('createAddressFormData'));
        if (savedData) {
            addressLineField.value = savedData.address_line || '';
            cityField.value = savedData.city || '';
            provinceField.value = savedData.province || '';
            countryField.value = savedData.country || '';
            postalCodeField.value = savedData.postal_code || '';
        }

        // Clear saved data when form is submitted
        document.querySelector('form').addEventListener('submit', function() {
            localStorage.removeItem('createAddressFormData');
        });
    });
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>