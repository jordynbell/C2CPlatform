<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
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
                        echo "Failed to insert new address: " . $new_address_stmt->error;
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
                } else {
                    echo "Failed to insert shipment: " . $shipment_stmt->error;
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
                echo "Failed to place order: " . $insert_stmt->error;
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';

?>

<?php if ($product_data): ?>
    <div class="container">
        <h1 class="text-center">Order Details</h1>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered" border="1">
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Seller Name</th>
                    <th>Seller Surname</th>
                </tr>
                <?php
                echo "<tr>";
                echo "<td>" . htmlspecialchars($product_data['title']) . "</td>";
                echo "<td>" . htmlspecialchars($product_data['description']) . "</td>";
                echo "<td>" . htmlspecialchars($product_data['category']) . "</td>";
                echo "<td>R " . htmlspecialchars($product_data['price']) . "</td>";
                echo "<td>" . htmlspecialchars($product_data['status']) . "</td>";
                echo "<td>" . htmlspecialchars($product_data['name']) . "</td>";
                echo "<td>" . htmlspecialchars($product_data['surname']) . "</td>";
                echo "</tr>";
                ?>
            </table>
        </div>
    </div>

    <form action="" method="post" class="container mt-4 mb-5">
        <input type="hidden" name="action" value="confirm">
        <input type="hidden" name="product_id" value="<?php echo $product_data['product_id'] ?>">
        <input type="hidden" name="price" value="<?php echo $product_data['price'] ?>">

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Delivery Options</h4>
            </div>
            <div class="card-body">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="delivery_method" id="delivery" value="Delivery"
                        checked>
                    <label class="form-check-label" for="delivery">Delivery</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="delivery_method" id="collection" value="Collection">
                    <label class="form-check-label" for="collection">Collection</label>
                </div>
            </div>
        </div>

        <div id="deliveryAddress" class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Delivery Address</h4>
            </div>
            <div class="card-body">

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
                    <h5>Or Enter a New Address:</h5>
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-12">
                        <label for="address_line" class="form-label">Address Line:</label>
                        <input type="text" class="form-control auto-capitalise" name="address_line" id="address_line"
                            placeholder="123 Steyn Road, Grape Village">
                    </div>
                    <div class="col-md-6">
                        <label for="city" class="form-label">City:</label>
                        <input type="text" class="form-control auto-capitalise" name="city" id="city"
                            placeholder="Cape Town">
                    </div>
                    <div class="col-md-6">
                        <label for="province" class="form-label">Province:</label>
                        <input type="text" class="form-control auto-capitalise" name="province" id="province"
                            placeholder="Western Cape">
                    </div>
                    <div class="col-md-6">
                        <label for="country" class="form-label">Country:</label>
                        <input type="text" class="form-control auto-capitalise" name="country" id="country"
                            placeholder="South Africa">
                    </div>
                    <div class="col-md-6">
                        <label for="postal_code" class="form-label">Postal Code:</label>
                        <input type="text" class="form-control" name="postal_code" id="postal_code" placeholder="4321" pattern="[0-9]{4,5}" maxlength="5" title="Enter digits only.">
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-lg">Confirm Order</button>
    </form>
<?php else: ?>
    <p>No product selected.</p>
<?php endif; ?>

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