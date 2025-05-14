<?php

require_once __DIR__ . '/../../lib/db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["Email"])) {
    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "Payment - Squito";

$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : 0;
$amount = isset($_POST['price']) ? $_POST['price'] : null;
$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;

if (!isset($_POST['order_id']) || !isset($_POST['product_id']) || !isset($_POST['price'])) {
    // Log the error
    error_log("Missing required payment parameters: " . json_encode($_POST));
    // Redirect back with error
    header("Location: ../index.php?error=missing_payment_parameters");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $payment_date = (new DateTime('now', new DateTimeZone('GMT+2')))->format('Y-m-d H:i:s');
    if ($order_id <= 0) {
        header("Location: ../order/index.php");
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] == 'confirm') {
        $stmt = $conn->prepare('INSERT INTO payment (order_id, payment_date, amount) VALUES (?, ?, ?)');
        $stmt->bind_param("isi", $order_id, $payment_date, $amount);

        if ($stmt->execute()) {
            $stmt->close();

            $update_stmt = $conn->prepare('UPDATE `order` SET status = ? WHERE order_id = ?');
            $new_status = 'Paid';
            $update_stmt->bind_param("si", $new_status, $order_id);
            if ($update_stmt->execute()) {
                $update_stmt->close();

                $cancel_stmt = $conn->prepare('UPDATE `order` SET status = ? WHERE product_id = ? AND order_id != ? AND status != "Paid"');
                $cancel_status = 'Cancelled';
                $cancel_stmt->bind_param("sii", $cancel_status, $product_id, $order_id);
                if ($cancel_stmt->execute()) {
                    $cancel_stmt->close();
                } else {
                    echo "Failed to cancel order: " . $cancel_stmt->error;
                }
            } else {
                echo "Failed to update order status: " . $update_stmt->error;
            }

            $update_stmt = $conn->prepare('UPDATE product SET status = ? WHERE product_id = ?');
            $new_status = 'Sold';
            $update_stmt->bind_param("si", $new_status, $product_id);
            if ($update_stmt->execute()) {
                $update_stmt->close();
                header("Location: ../index.php");
            } else {
                echo "Failed to update product status: " . $update_stmt->error;
            }

            $shipment_stmt = $conn->prepare('UPDATE shipment SET delivery_status = ?, shipment_date = ? where order_id = ?');
            $shipment_status = 'Shipped';
            $shipment_date = (new DateTime('now', new DateTimeZone('GMT+2')))->format('Y-m-d H:i:s');
            $shipment_stmt->bind_param("ssi", $shipment_status, $shipment_date, $order_id);

            if ($shipment_stmt->execute()) {
                $shipment_stmt->close();
            } else {
                echo "Failed to update shipment status: " . $shipment_stmt->error;
            }

            $sale_stmt = $conn->prepare('INSERT INTO sale (product_id, price, date_sold) VALUES (?, ?, ?)');
            $sale_stmt->bind_param("ids", $product_id, $amount, $payment_date);
            if ($sale_stmt->execute()) {
                $sale_stmt->close();
            } else {
                echo "Failed to insert sale record: " . $sale_stmt->error;
            }

            header("Location: ../order/index.php");
        } else {
            echo "Failed to process payment: " . $stmt->error;
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="container mt-4 mb-5 d-flex flex-column align-items-center" style="width: 30%;">
    <form action="" method="post" class="bg-light p-4 shadow-sm rounded">
        <h1>Make Payment</h1>

        <div class="mb-2">
            <label for="Card Name"></label>
            <input type="text" name="Card Name" id="cardName" placeholder="John Doe" class="form-control auto-capitalise" required>
        </div>

        <div class="mb-2">
            <label for="Card Number"></label>
            <input type="text" name="Card Number" id="cardNumber" placeholder="1234 5678 9012 3456" class="form-control"
                required>
        </div>

        <div class="mb-2">
            <label for="Expiry Date"></label>
            <input type="text" name="Expiry Date" id="expiryDate" placeholder="MM/YY" class="form-control" required>
        </div>

        <div class="mb-2">
            <label for="CVV"></label>
            <input type="text" name="CVV" id="cvv" placeholder="123" class="form-control" required>
        </div>

        <div class="mb-2">
            <label for="price">Total:</label>
            <input type="text" name="price" id="price" value="<?php echo "R " . number_format($amount,) ?>" class="form-control" readonly><br>
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

<!-- Card Input validation
     Did not add Luhn's algorithm for card number validation as it is unecessary for the prototype.
-->
<script>
    const cardNumberInput = document.getElementById('cardNumber');
    const expiryDateInput = document.getElementById('expiryDate');
    const cvvInput = document.getElementById('cvv');
    const cardNameInput = document.getElementById('cardName');

    cardNumberInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim();
    });
    expiryDateInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').replace(/(.{2})/, '$1/').trim();
    });
    cvvInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
    cardNameInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
    });

    cardNumberInput.addEventListener('input', function() {
        if (this.value.length > 19) {
            this.value = this.value.slice(0, 19);
        }
    });
    expiryDateInput.addEventListener('input', function() {
        if (this.value.length > 5) {
            this.value = this.value.slice(0, 5);
        }
    });
    cvvInput.addEventListener('input', function() {
        if (this.value.length > 3) {
            this.value = this.value.slice(0, 3);
        }
    });
    cardNameInput.addEventListener('input', function() {
        if (this.value.length > 50) {
            this.value = this.value.slice(0, 50);
        }
    });


</script>
<?php
require_once __DIR__ . '/../../includes/footer.php';
?>