<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "Edit Address - Squito";

// Check if address ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$address_id = $_GET['id'];
$user_id = $_SESSION["User_ID"];

// Fetch the address data
$stmt = $conn->prepare("SELECT * FROM address WHERE address_id = ? AND user_id = ?");
$stmt->bind_param("ii", $address_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Address not found or doesn't belong to current user
    header("Location: index.php");
    exit;
}

$address = $result->fetch_assoc();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address_line = $_POST["address_line"];
    $city = $_POST["city"];
    $province = $_POST["province"];
    $country = $_POST["country"];
    $postal_code = $_POST["postal_code"];

    $update_stmt = $conn->prepare("UPDATE address SET address_line = ?, city = ?, province = ?, country = ?, postal_code = ? WHERE address_id = ? AND user_id = ?");
    $update_stmt->bind_param("sssssii", $address_line, $city, $province, $country, $postal_code, $address_id, $user_id);

    if ($update_stmt->execute()) {
        // Set toast success messages
        $_SESSION['toast_message'] = "Address edited successfully!";
        $_SESSION['toast_type'] = "success";

        header("Location: index.php");
        exit;
    } else {
        // Set toast error messages
        $_SESSION['toast_message'] = "Failed to edit address. Please try again.";
        $_SESSION['toast_type'] = "error";

        $error = "Failed to update address. Please try again.";
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mx-auto mt-5 mb-5" style="max-width: 60rem;">
    <h1 class="text-center mb-4">Edit Address</h1>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border rounded p-4">
                <form action="" method="post">
                    <div class="mb-3 mt-2">
                        <label for="address_line" class="form-label">Address Line:</label>
                        <input type="text" class="form-control auto-capitalise" name="address_line" id="address_line"
                            placeholder="123 Steyn Road, Grape Village" value="<?php echo $address["address_line"]; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="city" class="form-label">City:</label>
                        <input type="text" class="form-control auto-capitalise" name="city" id="city"
                            placeholder="Cape Town" value="<?php echo $address["city"]; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="province" class="form-label">Province:</label>
                        <input type="text" class="form-control auto-capitalise" name="province" id="province"
                            placeholder="Western Cape" value="<?php echo $address["province"]; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="country" class="form-label">Country:</label>
                        <input type="text" class="form-control auto-capitalise" name="country" id="country"
                            placeholder="South Africa" value="<?php echo $address["country"]; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="postal_code" class="form-label">Postal Code:</label>
                        <input type="text" class="form-control" name="postal_code" id="postal_code" placeholder="4321" pattern="[0-9]{4,5}" maxlength="5" title="Enter digits only." value="<?php echo $address["postal_code"]; ?>" required>
                    </div>
                    <div class="mb-3 text-center">
                        <button type="submit" class="btn btn-primary">Edit Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Confirm Changes</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to save these changes?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmEdit">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
        const confirmButton = document.getElementById('confirmEdit');
        const form = document.querySelector('form');
        
        // Add a button to trigger the modal
        const submitBtn = document.querySelector('button[type="submit"]');
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Check if form is valid before showing modal
            if (form.checkValidity()) {
                modal.show();
            } else {
                form.reportValidity();
            }
        });

        confirmButton.addEventListener('click', function() {
            form.submit();
            modal.hide();
        });
    });
</script>

<script>
    // Save form data as user types
    document.addEventListener('DOMContentLoaded', function () {
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

            localStorage.setItem('editAddressFormData', JSON.stringify(formData));
        }

        // Add input event listeners to all fields
        addressLineField.addEventListener('input', saveFormData);
        cityField.addEventListener('input', saveFormData);
        provinceField.addEventListener('input', saveFormData);
        countryField.addEventListener('input', saveFormData);
        postalCodeField.addEventListener('input', saveFormData);

        // Load saved form data if it exists
        const savedData = JSON.parse(localStorage.getItem('editAddressFormData'));
        if (savedData) {
            addressLineField.value = savedData.address_line || '';
            cityField.value = savedData.city || '';
            provinceField.value = savedData.province || '';
            countryField.value = savedData.country || '';
            postalCodeField.value = savedData.postal_code || '';
        }

        // Clear saved data when form is submitted
        document.querySelector('form').addEventListener('submit', function () {
            localStorage.removeItem('editAddressFormData');
        });
    });
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>