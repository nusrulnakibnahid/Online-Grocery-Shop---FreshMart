<?php
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = intval($_GET['id']);

// Fetch order details
$stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Fetch order items
$stmt = $pdo->prepare("SELECT oi.*, p.name, p.photo_url FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

// Calculate all amounts with precise decimal handling
$subtotal = 0.00;
$item_subtotals = [];

foreach ($order_items as $item) {
    $item_subtotal = round($item['price'] * $item['quantity'], 2);
    $item_subtotals[] = $item_subtotal;
    $subtotal += $item_subtotal;
}

$subtotal = round($subtotal, 2);
$shipping_fee = 50.00;
$tax_rate = 0.10;
$tax_amount = round($subtotal * $tax_rate, 2);
$calculated_total = round($subtotal + $shipping_fee + $tax_amount, 2);

// Use database total if it exists, otherwise use calculated total
$display_total = isset($order['total_amount']) ? round($order['total_amount'], 2) : $calculated_total;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - FreshMart</title>
    <link rel="icon" type="image/png" href="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT3aAAKqYeAJ85UjxrgA4ZiQtpaDju-UTez55LckWFBFu9_VpSMWFClskEprIv-x8S-L3U&usqp=CAU">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-5 mb-5">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h2>Order Confirmation</h2>
                <p class="mb-0">Thank you for your order!</p>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <h4 class="alert-heading">Your order has been placed successfully!</h4>
                    <p>Order ID: #<?php echo $order['id']; ?></p>
                    <p>We've sent a confirmation email to <?php echo htmlspecialchars($order['email']); ?></p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h4>Order Details</h4>
                        <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                        <p><strong>Status:</strong> <span class="badge bg-info"><?php echo ucfirst($order['status']); ?></span></p>
                        <p><strong>Payment Method:</strong> <?php echo strtoupper($order['payment_method']); ?></p>
                        <p><strong>Total Amount:</strong> ৳<?php echo number_format($display_total, 2); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h4>Shipping Address</h4>
                        <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    </div>
                </div>
                
                <hr>
                
                <h4>Order Items</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $index => $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($item['photo_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                 class="img-thumbnail me-3" width="80">
                                            <div><?php echo htmlspecialchars($item['name']); ?></div>
                                        </div>
                                    </td>
                                    <td>৳<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>৳<?php echo number_format($item_subtotals[$index], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td>৳<?php echo number_format($subtotal, 2); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                <td>৳<?php echo number_format($shipping_fee, 2); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Tax (10%):</strong></td>
                                <td>৳<?php echo number_format($tax_amount, 2); ?></td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td>৳<?php echo number_format($display_total, 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="text-center mt-4">
                    <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                    <a href="orders.php" class="btn btn-outline-secondary ms-2">View All Orders</a>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>