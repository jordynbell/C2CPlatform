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
        header("Location: index.php");
        exit;
    } else {
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

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>