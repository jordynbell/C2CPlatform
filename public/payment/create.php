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

    // Redirect to login page
    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "Payment - Squito";

// Set variables
$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : 0;
$amount = isset($_POST['price']) ? $_POST['price'] : null;
$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;

if (!isset($_POST['order_id']) || !isset($_POST['product_id']) || !isset($_POST['price'])) {

    // Set toast error messages
    $_SESSION['toast_message'] = "Invalid request.";
    $_SESSION['toast_type'] = "danger";

    $conn->close();

    // Redirect to order page
    header("Location: ../order/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $payment_date = (new DateTime('now', new DateTimeZone('GMT+2')))->format('Y-m-d H:i:s');
    if ($order_id <= 0) {
        // Set toast error messages
        $_SESSION['toast_message'] = "Invalid order ID.";
        $_SESSION['toast_type'] = "danger";

        $conn->close();

        // Redirect to order page
        header("Location: ../order/index.php");
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] == 'confirm') {
        // Select all the order details for the order ID
        $check_order_exists_stmt = $conn->prepare('SELECT * FROM `order` WHERE order_id = ?');
        $check_order_exists_stmt->bind_param("i", $order_id);
        $check_order_exists_stmt->execute();
        $result = $check_order_exists_stmt->get_result();
        if ($result->num_rows == 0) {
            $check_order_exists_stmt->close();

            // Set toast error messages
            $_SESSION['toast_message'] = "Order not found.";
            $_SESSION['toast_type'] = "danger";

            $conn->close();

            // Order does not exist
            header("Location: ../order/index.php");
            exit;
        }
        $check_order_exists_stmt->close();

        // Check if the order is already paid
        $check_paid_stmt = $conn->prepare('SELECT * FROM `order` WHERE order_id = ? AND status = "Paid"');
        $check_paid_stmt->bind_param("i", $order_id);
        $check_paid_stmt->execute();
        $result = $check_paid_stmt->get_result();
        $check_paid_stmt->close();

        if ($result->num_rows > 0) {

            // Set toast error messages
            $_SESSION['toast_message'] = "Order already paid.";
            $_SESSION['toast_type'] = "warning";

            $conn->close();

            // Order already paid
            header("Location: ../order/index.php");
            exit;
        }

        // Insert payment details into payment table.
        $stmt = $conn->prepare('INSERT INTO payment (order_id, payment_date, amount) VALUES (?, ?, ?)');
        $stmt->bind_param("isi", $order_id, $payment_date, $amount);

        if ($stmt->execute()) {
            $stmt->close();

            $update_stmt = $conn->prepare('UPDATE `order` SET status = ? WHERE order_id = ?');
            $new_status = 'Paid';
            $update_stmt->bind_param("si", $new_status, $order_id);
            if ($update_stmt->execute()) {
                $update_stmt->close();

                // Cancel other orders for the same product
                $cancel_stmt = $conn->prepare('UPDATE `order` SET status = ? WHERE product_id = ? AND order_id != ? AND status != "Paid"');
                $cancel_status = 'Cancelled';
                $cancel_stmt->bind_param("sii", $cancel_status, $product_id, $order_id);
                if ($cancel_stmt->execute()) {

                    $cancel_stmt->close();
                } else {
                    $conn->close();

                    header("Location: failed.php?order_id=" . $order_id . "&reason=payment_declined");
                    exit;
                }
            } else {

                $conn->close();

                header("Location: failed.php?order_id=" . $order_id . "&reason=payment_declined");
                exit;
            }

            // Update product status to sold
            $update_stmt = $conn->prepare('UPDATE product SET status = ? WHERE product_id = ?');
            $new_status = 'Sold';
            $update_stmt->bind_param("si", $new_status, $product_id);
            if ($update_stmt->execute()) {
                $update_stmt->close();
            } else {
                $conn->close();

                header("Location: failed.php?order_id=" . $order_id . "&reason=payment_declined");
                exit;
            }

            // Update shipment status to shipped and set shipment date
            $shipment_stmt = $conn->prepare('UPDATE shipment SET delivery_status = ?, shipment_date = ? where order_id = ?');
            $shipment_status = 'Shipped';
            $shipment_date = (new DateTime('now', new DateTimeZone('GMT+2')))->format('Y-m-d H:i:s');
            $shipment_stmt->bind_param("ssi", $shipment_status, $shipment_date, $order_id);

            if ($shipment_stmt->execute()) {
                $shipment_stmt->close();
            } else {
                $conn->close();

                header("Location: failed.php?order_id=" . $order_id . "&reason=payment_declined");
                exit;
            }

            // Cancel other shipments for the same product
            $shipment_cleanup_stmt = $conn->prepare('UPDATE shipment s JOIN `order` o ON s.order_id = o.order_id SET s.delivery_status = ? WHERE o.product_id = ? AND s.order_id != ? AND s.delivery_status != "Cancelled"');
            $cancel_status = 'Cancelled';
            $shipment_cleanup_stmt->bind_param("sii", $cancel_status, $product_id, $order_id);

            if ($shipment_cleanup_stmt->execute()) {
                $shipment_cleanup_stmt->close();
            } else {
                $conn->close();

                header("Location: failed.php?order_id=" . $order_id . "&reason=payment_declined");
                exit;
            }

            // Insert sale details into sale table once all other operations are successful
            $sale_stmt = $conn->prepare('INSERT INTO sale (product_id, price, date_sold) VALUES (?, ?, ?)');
            $sale_stmt->bind_param("ids", $product_id, $amount, $payment_date);
            if ($sale_stmt->execute()) {
                $sale_stmt->close();

                $conn->close();

                header("Location: success.php?order_id=" . $order_id);
                exit;
            } else {
                $conn->close();

                header("Location: failed.php?order_id=" . $order_id . "&reason=payment_declined");
                exit;
            }

        } else {
            $conn->close();
          
            header("Location: failed.php?order_id=" . $order_id . "&reason=payment_declined");
            exit;
        }
    } else {
        // Set toast error messages
        $_SESSION['toast_message'] = "Invalid request.";
        $_SESSION['toast_type'] = "danger";

        $conn->close();

        header("Location: ../order/index.php");
        exit;
    }
}

$conn->close();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container mt-4 mb-5 d-flex flex-column align-items-center" style="width: 30%;">
    <form action="" method="post" class="bg-light p-4 shadow-sm rounded needs-validation" novalidate>
        <h1>Make Payment</h1>

        <div class="mb-2">
            <label for="cardName" class="form-label">Cardholder Name</label>
            <input type="text" name="Card Name" id="cardName" placeholder="John Doe"
                class="form-control auto-capitalise" pattern="[a-zA-Z\s]+" minlength="2" maxlength="50" required>
            <div class="invalid-feedback">
                Please enter a valid name (min 2 characters).
            </div>
        </div>

        <div class="mb-2">
            <label for="cardNumber" class="form-label">Card Number</label>
            <input type="text" name="Card Number" id="cardNumber" placeholder="1234 5678 9012 3456" class="form-control"
                pattern="\d{4}\s\d{4}\s\d{4}\s\d{4}" required>
            <div class="invalid-feedback">
                Please enter a valid 16-digit card number.
            </div>
        </div>

        <div class="mb-2">
            <label for="expiryDate" class="form-label">Expiry Date</label>
            <input type="text" name="Expiry Date" id="expiryDate" placeholder="MM/YY" class="form-control"
                pattern="(0[1-9]|1[0-2])\/([0-9]{2})" required>
            <div class="invalid-feedback">
                Please enter a valid expiry date (MM/YY).
            </div>
        </div>

        <div class="mb-2">
            <label for="cvv" class="form-label">CVV</label>
            <input type="text" name="CVV" id="cvv" placeholder="123" class="form-control" pattern="\d{3}" required>
            <div class="invalid-feedback">
                Please enter a valid 3-digit CVV.
            </div>
        </div>

        <div class="mb-2">
            <label for="price">Total:</label>
            <input type="text" name="price" id="price" value="<?php echo "R " . number_format($amount, 2) ?>"
                class="form-control" readonly><br>
        </div>

        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
        <input type="hidden" name="price" value="<?php echo $amount; ?>">
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <input type="hidden" name="action" value="confirm">

        <div class="mb-2 text-center">
            <input type="submit" value="Confirm Payment" class="btn btn-primary">
        </div>
    </form>
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
    // Simple formatting code for card fields
    const cardNumberInput = document.getElementById('cardNumber');
    const expiryDateInput = document.getElementById('expiryDate');
    const cvvInput = document.getElementById('cvv');

    cardNumberInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim();
        if (this.value.length > 19) {
            this.value = this.value.slice(0, 19);
        }
    });

    expiryDateInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').replace(/(.{2})/, '$1/').trim();
        if (this.value.length > 5) {
            this.value = this.value.slice(0, 5);
        }
    });

    cvvInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
        if (this.value.length > 3) {
            this.value = this.value.slice(0, 3);
        }
    });

    // Bootstrap's built-in form validation
    (() => {
        'use strict'

        // Fetch all forms we want to apply validation to
        const forms = document.querySelectorAll('.needs-validation')

        // Loop over them and prevent submission
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>
<?php
require_once __DIR__ . '/../../includes/footer.php';
?>