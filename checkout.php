<?php
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_message'] = 'Please login to proceed to checkout';
    $_SESSION['flash_type'] = 'danger';
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['flash_message'] = 'Your cart is empty';
    $_SESSION['flash_type'] = 'warning';
    header('Location: cart.php');
    exit;
}

$errors = [];
$success = false;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $payment_method = $_POST['payment_method'];

    if (empty($first_name)) {
        $errors['first_name'] = 'First name is required';
    }

    if (empty($last_name)) {
        $errors['last_name'] = 'Last name is required';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email';
    }

    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required';
    }

    if (empty($address)) {
        $errors['address'] = 'Address is required';
    }

    if (empty($payment_method)) {
        $errors['payment_method'] = 'Payment method is required';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $total = 0;
            $product_ids = array_keys($_SESSION['cart']);
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));

            $stmt = $pdo->prepare("SELECT product_id, price FROM products WHERE product_id IN ($placeholders)");
            $stmt->execute($product_ids);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($products as $product) {
                $total += $product['price'] * 10 * $_SESSION['cart'][$product['product_id']]['quantity'];
            }

            $shipping = 50.00;
            $tax = $total * 0.1;
            $grand_total = $total + $shipping + $tax;

            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, payment_method, shipping_address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                $grand_total,
                'pending',
                $payment_method,
                $address
            ]);
            $order_id = $pdo->lastInsertId();

            foreach ($products as $product) {
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $order_id,
                    $product['product_id'],
                    $_SESSION['cart'][$product['product_id']]['quantity'],
                    $product['price'] * 10
                ]);

                $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                $stmt->execute([
                    $_SESSION['cart'][$product['product_id']]['quantity'],
                    $product['product_id']
                ]);
            }

            $pdo->commit();

            unset($_SESSION['cart']);

            if ($first_name !== $user['first_name'] || $last_name !== $user['last_name'] || $email !== $user['email'] || $phone !== $user['phone']) {
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$first_name, $last_name, $email, $phone, $_SESSION['user_id']]);
            }

            $success = true;
            $_SESSION['flash_message'] = 'Order placed successfully! Thank you for your purchase.';
            $_SESSION['flash_type'] = 'success';
            header('Location: order_confirmation.php?id=' . $order_id);
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors['database'] = 'Error processing your order: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - FreshMart</title>
    <link rel="icon" type="image/png" href="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT3aAAKqYeAJ85UjxrgA4ZiQtpaDju-UTez55LckWFBFu9_VpSMWFClskEprIv-x8S-L3U&usqp=CAU">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-5 mb-5">
        <h2 class="mb-4">Checkout</h2>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Shipping Information</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="checkout.php">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name"
                                        value="<?php echo htmlspecialchars($_POST['first_name'] ?? $user['first_name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name"
                                        value="<?php echo htmlspecialchars($_POST['last_name'] ?? $user['last_name'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                    value="<?php echo htmlspecialchars($_POST['phone'] ?? $user['phone'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Shipping Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?php
                                    echo htmlspecialchars($_POST['address'] ?? $user['address'] ?? '');
                                ?></textarea>
                            </div>

                            <h5 class="mt-4">Payment Method</h5>
                            
                            <!-- Cash on Delivery -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                                <label class="form-check-label d-flex align-items-center" for="cod">
                                    <img src="https://static-00.iconduck.com/assets.00/cash-on-delivery-icon-1024x345-7sgjf338.png" alt="Cash on Delivery Icon" style="height: 20px; margin-right: 8px;">
                                    Cash on Delivery (COD)
                                </label>
                            </div>

                            <!-- Credit Card -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card">
                                <label class="form-check-label d-flex align-items-center" for="credit_card">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/Visa_Logo.png" alt="Credit Card Logo" style="height: 20px; margin-right: 8px;">
                                    Credit Card
                                </label>
                            </div>

                            <!-- Additional fields for Credit Card Information -->
                            <div id="credit_card_info" class="mt-4 mb-5" style="display: none;">
                                <div class="form-group mb-3">
                                    <label for="card_number">Card Number</label>
                                    <input type="text" class="form-control" id="card_number" placeholder="Enter your card number">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="expiration_date">Expiration Date</label>
                                    <input type="month" class="form-control" id="expiration_date">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="cvv">CVV</label>
                                    <input type="text" class="form-control" id="cvv" placeholder="Enter CVV">
                                </div>
                            </div>

                            <!-- Bkash -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="payment_method" id="bkash" value="bkash">
                                <label class="form-check-label d-flex align-items-center" for="bkash">
                                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTNLzHK-SV3u-30vKet9pEkjGbiNv5YOmJIoA&s" alt="Bkash Logo" style="height: 35px; margin-right: 8px;">
                                    Bkash
                                </label>
                            </div>

                            <!-- Additional fields for Bkash Payment -->
                            <div id="bkash_info" class="mt-3 mb-5" style="display: none;">
                                <div class="alert alert-info mb-3">
                                    Please send payment to this number: <strong>01612605228</strong>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="bkash_phone">Your Bkash Number</label>
                                    <input type="tel" class="form-control" id="bkash_phone" name="bkash_phone" placeholder="Enter your Bkash phone number" pattern="[0-9]{11}">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="bkash_txn">Transaction ID</label>
                                    <input type="text" class="form-control" id="bkash_txn" name="bkash_txn" placeholder="Enter Transaction ID">
                                </div>
                            </div>

                            <!-- Nagad -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="payment_method" id="nagad" value="nagad">
                                <label class="form-check-label d-flex align-items-center" for="nagad">
                                    <img src="https://download.logo.wine/logo/Nagad/Nagad-Logo.wine.png" alt="Nagad Logo" style="height: 45px; margin-right: 8px;">
                                    Nagad
                                </label>
                            </div>

                            <!-- Additional fields for Nagad Payment -->
                            <div id="nagad_info" class="mt-3 mb-5" style="display: none;">
                                <div class="alert alert-info mb-3">
                                    Please send payment to this number: <strong>01612605228</strong>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="nagad_phone">Your Nagad Number</label>
                                    <input type="tel" class="form-control" id="nagad_phone" name="nagad_phone" placeholder="Enter your Nagad phone number" pattern="[0-9]{11}">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="nagad_txn">Transaction ID</label>
                                    <input type="text" class="form-control" id="nagad_txn" name="nagad_txn" placeholder="Enter Transaction ID">
                                </div>
                            </div>

                            <!-- Rocket -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="payment_method" id="rocket" value="rocket">
                                <label class="form-check-label d-flex align-items-center" for="rocket">
                                    <img src="https://images.seeklogo.com/logo-png/31/1/dutch-bangla-rocket-logo-png_seeklogo-317692.png" alt="Rocket Logo" style="height: 45px; margin-right: 8px;">
                                    Rocket
                                </label>
                            </div>

                            <!-- Additional fields for Rocket Payment -->
                            <div id="rocket_info" class="mt-3 mb-5" style="display: none;">
                                <div class="alert alert-info mb-3">
                                    Please send payment to this number: <strong>+8801612605228</strong>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="rocket_phone">Your Rocket Number</label>
                                    <input type="tel" class="form-control" id="rocket_phone" name="rocket_phone" placeholder="Enter your Rocket phone number" pattern="[0-9]{11}">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="rocket_txn">Transaction ID</label>
                                    <input type="text" class="form-control" id="rocket_txn" name="rocket_txn" placeholder="Enter Transaction ID">
                                </div>
                            </div>

                            <script>
                                const paymentMethods = ['credit_card', 'bkash', 'nagad', 'rocket'];

                                paymentMethods.forEach(method => {
                                    document.getElementById(method).addEventListener("change", function() {
                                        paymentMethods.forEach(m => {
                                            const infoSection = document.getElementById(`${m}_info`);
                                            if (infoSection) {
                                                infoSection.style.display = (m === method && this.checked) ? "block" : "none";
                                            }
                                        });
                                    });
                                });
                            </script>

                            <button type="submit" class="btn btn-primary w-100">Place Order</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Order Summary</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        $total = 0;
                        $product_ids = array_keys($_SESSION['cart']);
                        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));

                        $stmt = $pdo->prepare("SELECT product_id, name, price FROM products WHERE product_id IN ($placeholders)");
                        $stmt->execute($product_ids);
                        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($products as $product):
                            $quantity = $_SESSION['cart'][$product['product_id']]['quantity'];
                            $subtotal = $product['price']  * $quantity;
                            $total += $subtotal;
                        ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo htmlspecialchars($product['name']); ?> × <?php echo $quantity; ?></span>
                                <span>৳<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                        <?php endforeach; ?>

                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>৳<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span>৳50.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (10%):</span>
                            <span>৳<?php echo number_format($total * 0.1, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total:</span>
                            <span>৳<?php echo number_format($total + 50 + ($total * 0.1), 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>