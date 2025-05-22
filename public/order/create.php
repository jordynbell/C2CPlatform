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

$pageTitle = "Create Order - Squito";

$user_id = $_SESSION['User_ID'];
$product_data = null;
$addresses = null;

$address_stmt = $conn->prepare('SELECT * FROM address WHERE user_id = ? AND isActive = 1');
$address_stmt->bind_param("i", $user_id);
$address_stmt->execute();
$address_result = $address_stmt->get_result();
$addresses = $address_result->fetch_all(MYSQLI_ASSOC);

$address_stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    $stmt = $conn->prepare('SELECT product_id, product.title, product.description, product.category, product.price, product.status, product.seller_id, user.name, user.surname FROM product INNER JOIN user ON product.seller_id = user.user_id WHERE product.product_id = ?');
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product_data = $result->fetch_assoc();

    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;

    if ($product_id) {

        $stmt = $conn->prepare('SELECT product_id, product.title, product.description, product.category, product.price, product.status, product.seller_id, user.name, user.surname FROM product INNER JOIN user ON product.seller_id = user.user_id WHERE product.product_id = ?');
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product_data = $result->fetch_assoc();

        $stmt->close();

        if (isset($_POST['action']) && $_POST['action'] == 'confirm') {
            $delivery_method = $_POST['delivery_method'];
            $address_id = null;
            if ($delivery_method == 'Delivery') {
                if (!empty($_POST['existing_address'])) {
                    $address_id = $_POST['existing_address'];
                } else {
                    $new_address_stmt = $conn->prepare('INSERT INTO address (user_id, address_line, city, province, country, postal_code) VALUES (?, ?, ?, ?, ?, ?)');
                    $new_address_stmt->bind_param("issssi", $user_id, $_POST['address_line'], $_POST['city'], $_POST['province'], $_POST['country'], $_POST['postal_code']);
                    if ($new_address_stmt->execute()) {
                        $address_id = $new_address_stmt->insert_id;
                        
                        $new_address_stmt->close();
                    } else {
                        $new_address_stmt->close();

                        // Set toast error messages
                        $_SESSION['toast_message'] = "Failed to insert address: " . $new_address_stmt->error;
                        $_SESSION['toast_type'] = "danger";

                        $conn->close();

                        header("Location: index.php");
                        exit;
                    }
                }
            }


            $order_date = (new DateTime('now', new DateTimeZone('GMT+2')))->format('Y-m-d H:i:s');
            $price = $product_data['price'];
            $status = 'Pending payment';

            $insert_stmt = $conn->prepare('INSERT INTO `order` (order_date, price, status, customer_id, product_id) VALUES(?,?,?,?,?)');
            $insert_stmt->bind_param("sdsii", $order_date, $price, $status, $user_id, $product_id);

            if ($insert_stmt->execute()) {
                $order_id = $insert_stmt->insert_id;
                $insert_stmt->close();

                $delivery_method = $_POST['delivery_method'];
                $delivery_status = 'Pending payment';

                if ($delivery_method == 'Delivery') {
                    $shipment_stmt = $conn->prepare('INSERT INTO shipment (order_id, address_id, delivery_method, delivery_status) VALUES (?, ?, ?, ?)');
                    $shipment_stmt->bind_param("iiss", $order_id, $address_id, $delivery_method, $delivery_status);
                } else {
                    $shipment_stmt = $conn->prepare('INSERT INTO shipment (order_id, delivery_method, delivery_status) VALUES (?, ?, ?)');
                    $shipment_stmt->bind_param("iss", $order_id, $delivery_method, $delivery_status);
                }
                if ($shipment_stmt->execute()) {
                    $shipment_stmt->close();

                    // Set toast success messages
                    $_SESSION['toast_message'] = "Order placed successfully!";
                    $_SESSION['toast_type'] = "success";
                } else {
                    $shipment_stmt->close();

                    // Set toast error messages
                    $_SESSION['toast_message'] = "Failed to insert shipment: " . $shipment_stmt->error;
                    $_SESSION['toast_type'] = "danger";

                    $conn->close();

                    header("Location: index.php");
                    exit;
                }

                echo '
                        <form id="redirectToPaymentForm" action="../payment/create.php" method="post">
                            <input type="hidden" name="order_id" value="' . $order_id . '">
                            <input type="hidden" name="price" value="' . $price . '">
                            <input type="hidden" name="product_id" value="' . $product_id . '">
                        </form>
                        <script>
                            document.getElementById("redirectToPaymentForm").submit();
                        </script>
                    ';
                exit;
            } else {
                $insert_stmt->close();

                // Set toast error messages
                $_SESSION['toast_message'] = "Failed to insert order: " . $insert_stmt->error;
                $_SESSION['toast_type'] = "danger";

                $conn->close();

                header("Location: index.php");
                exit;
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';

?>

<?php if ($product_data): ?>
    <div class="container my-5">
        <h1 class="text-center mb-4">Complete Your Order</h1>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                    <div class="card-header bg-primary text-white py-3">
                        <h4 class="mb-0">Product Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3 mb-md-0 text-center">
                                <img src="../listing/getImage.php?id=<?php echo htmlspecialchars($product_data['product_id']); ?>"
                                    class="img-fluid rounded" style="max-height: 180px; object-fit: contain;"
                                    alt="<?php echo htmlspecialchars($product_data['title']); ?>">
                            </div>
                            <div class="col-md-9">
                                <h3 class="mb-2"><?php echo htmlspecialchars($product_data['title']); ?></h3>
                                <p class="text-muted mb-3"><?php echo htmlspecialchars($product_data['description']); ?></p>

                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <strong>Category:</strong>
                                        <span
                                            class="badge bg-secondary"><?php echo htmlspecialchars($product_data['category']); ?></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <strong>Price:</strong>
                                        <span class="fs-5 text-primary fw-bold">R
                                            <?php echo htmlspecialchars(number_format($product_data['price'], 2)); ?></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <strong>Seller:</strong>
                                        <span><?php echo htmlspecialchars($product_data['name'] . " " . $product_data['surname']); ?></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <strong>Status:</strong>
                                        <span
                                            class="badge bg-success"><?php echo htmlspecialchars($product_data['status']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form action="" method="post" class="mt-4 mb-5">
            <input type="hidden" name="action" value="confirm">
            <input type="hidden" name="product_id" value="<?php echo $product_data['product_id'] ?>">
            <input type="hidden" name="price" value="<?php echo $product_data['price'] ?>">

            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-primary text-white py-2">
                    <h5 class="mb-0">Delivery Options</h5>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-2 p-2 border rounded-3 bg-light">
                                <input class="form-check-input" type="radio" name="delivery_method" id="delivery"
                                    value="Delivery" checked>
                                <label class="form-check-label d-block ms-2" for="delivery">
                                    <div class="fw-bold">Delivery</div>
                                    <small class="text-muted">We'll ship this item to your address</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-2 p-2 border rounded-3 bg-light">
                                <input class="form-check-input" type="radio" name="delivery_method" id="collection"
                                    value="Collection">
                                <label class="form-check-label d-block ms-2" for="collection">
                                    <div class="fw-bold">Collection</div>
                                    <small class="text-muted">Pick up from the seller's location</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="deliveryAddress" class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-primary text-white py-2">
                    <h5 class="mb-0">Delivery Address</h5>
                </div>
                <div class="card-body p-3">
                    <?php if (count($addresses) > 0): ?>
                        <div class="mb-3">
                            <label for="existing_address" class="form-label">Select Existing Address:</label>
                            <select class="form-select" name="existing_address" id="existing_address">
                                <option value="">Select an address</option>
                                <?php foreach ($addresses as $address): ?>
                                    <option value="<?= $address['address_id']; ?>"
                                        data-line="<?= htmlspecialchars($address['address_line']); ?>"
                                        data-city="<?= htmlspecialchars($address['city']); ?>"
                                        data-province="<?= htmlspecialchars($address['province']); ?>"
                                        data-country="<?= htmlspecialchars($address['country']); ?>"
                                        data-postal="<?= htmlspecialchars($address['postal_code']); ?>">
                                        <?= htmlspecialchars(
                                            "{$address['address_line']}, {$address['city']}, {$address['province']}, {$address['country']}, {$address['postal_code']}"
                                        ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <hr class="my-3">
                        <h6 class="mb-2">Or Enter a New Address:</h6>
                    <?php endif; ?>

                    <div class="row g-2">
                        <div class="col-12">
                            <label for="address_line" class="form-label">Address Line:</label>
                            <input type="text" class="form-control auto-capitalise" name="address_line" id="address_line"
                                placeholder="123 Steyn Road, Grape Village">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="city" class="form-label">City:</label>
                            <input type="text" class="form-control auto-capitalise" name="city" id="city"
                                placeholder="Cape Town">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="province" class="form-label">Province:</label>
                            <input type="text" class="form-control auto-capitalise" name="province" id="province"
                                placeholder="Western Cape">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="country" class="form-label">Country:</label>
                            <input type="text" class="form-control auto-capitalise" name="country" id="country"
                                placeholder="South Africa">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="postal_code" class="form-label">Postal Code:</label>
                            <input type="text" class="form-control" name="postal_code" id="postal_code" placeholder="4321"
                                pattern="[0-9]{4,5}" maxlength="5">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-5 d-grid gap-2 align-self-center mx-auto">
                <button type="submit" class="btn btn-primary py-2">Confirm Order</button>
            </div>
        </form>
    <?php else: ?>
        <div class="container my-5 text-center">
            <div class="alert alert-warning p-5">
                <h3>No product selected</h3>
                <p class="mb-4">Please select a product to place an order.</p>
                <a href="../listing/index.php" class="btn btn-primary">Browse Products</a>
            </div>
        </div>
    <?php endif; ?>
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
        // Check if existing_address element exists before adding event listener
        const existingAddressSelect = document.getElementById('existing_address');
        if (existingAddressSelect) {
            existingAddressSelect.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                if (!this.value) {
                    document.getElementById('address_line').value = '';
                    document.getElementById('city').value = '';
                    document.getElementById('province').value = '';
                    document.getElementById('country').value = '';
                    document.getElementById('postal_code').value = '';
                    return;
                }

                document.getElementById('address_line').value = opt.dataset.line;
                document.getElementById('city').value = opt.dataset.city;
                document.getElementById('province').value = opt.dataset.province;
                document.getElementById('country').value = opt.dataset.country;
                document.getElementById('postal_code').value = opt.dataset.postal;
            });
        }

        const deliveryMethodRadios = document.querySelectorAll('input[name="delivery_method"]');
        const deliveryAddressDiv = document.getElementById('deliveryAddress');
        const addressFields = [
            document.getElementById('address_line'),
            document.getElementById('city'),
            document.getElementById('province'),
            document.getElementById('country'),
            document.getElementById('postal_code')
        ];

        function updateFieldsRequired() {
            const isDelivery = document.querySelector('input[name="delivery_method"]:checked').value === 'Delivery';
            const existingAddressSelect = document.getElementById('existing_address');
            const hasExistingAddress = existingAddressSelect && existingAddressSelect.value !== '';

            deliveryAddressDiv.style.display = isDelivery ? 'block' : 'none';

            addressFields.forEach(field => {
                if (field) {
                    field.required = isDelivery && !hasExistingAddress;
                }
            });
        }

        updateFieldsRequired();

        deliveryMethodRadios.forEach(radio => {
            radio.addEventListener('change', updateFieldsRequired);
        });

        if (existingAddressSelect) {
            existingAddressSelect.addEventListener('change', updateFieldsRequired);
        }
    });
</script>

<script>
    // Save form data as user types
    document.addEventListener('DOMContentLoaded', function () {
        // Get all form fields we want to save
        const existingAddressSelect = document.getElementById('existing_address');
        const address_lineField = document.getElementById('address_line');
        const cityField = document.getElementById('city');
        const provinceField = document.getElementById('province');
        const countryField = document.getElementById('country');
        const postal_codeField = document.getElementById('postal_code');


        // Function to save form data
        function saveFormData() {
            const formData = {
                existing_address: existingAddressSelect ? existingAddressSelect.value : '',
                address: address_lineField.value,
                city: cityField.value,
                province: provinceField.value,
                country: countryField.value,
                postal_code: postal_codeField.value
            };

            localStorage.setItem('addressFormData', JSON.stringify(formData));
        }

        // Add input event listeners to all fields
        if (existingAddressSelect) {
            existingAddressSelect.addEventListener('change', saveFormData);
        }
        address_lineField.addEventListener('input', saveFormData);
        cityField.addEventListener('input', saveFormData);
        provinceField.addEventListener('input', saveFormData);
        countryField.addEventListener('input', saveFormData);
        postal_codeField.addEventListener('input', saveFormData);

        // Load saved form data if it exists
        const savedData = JSON.parse(localStorage.getItem('addressFormData'));
        if (savedData) {
            if (existingAddressSelect) {
                existingAddressSelect.value = savedData.existing_address || '';
            }
            address_lineField.value = savedData.address || '';
            cityField.value = savedData.city || '';
            provinceField.value = savedData.province || '';
            countryField.value = savedData.country || '';
            postal_codeField.value = savedData.postal_code || '';
        }

        // Clear saved data when form is submitted
        document.querySelector('form').addEventListener('submit', function () {
            localStorage.removeItem('addressFormData');
        });
    });
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>