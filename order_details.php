<?php
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get order ID from URL
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verify the order belongs to the logged-in user and fetch order details
$order = null;
$order_items = [];
$error = '';

if ($order_id > 0) {
    try {
        // Fetch order header
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        $order = $stmt->fetch();
        
        if ($order) {
            // Fetch order items with product details
            $stmt = $pdo->prepare("
                SELECT oi.*, p.name, p.photo_url, p.price 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.product_id 
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll();
        } else {
            $error = "Order not found or you don't have permission to view it.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
} else {
    $error = "Invalid order ID.";
}

// Set page title
$page_title = $order ? "Order #{$order_id} Details - FreshMart" : 'Order Not Found - FreshMart';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" type="image/png" href="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT3aAAKqYeAJ85UjxrgA4ZiQtpaDju-UTez55LckWFBFu9_VpSMWFClskEprIv-x8S-L3U&usqp=CAU">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-5 mb-5">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mb-4">Order Details #<?php echo $order_id; ?></h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                    <div class="text-center mt-4">
                        <a href="orders.php" class="btn btn-primary">Back to Orders</a>
                    </div>
                <?php elseif (!$order): ?>
                    <div class="alert alert-warning">Order not found.</div>
                    <div class="text-center mt-4">
                        <a href="orders.php" class="btn btn-primary">Back to Orders</a>
                    </div>
                <?php else: ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-0">Order Information</h5>
                                </div>
                                <div class="col-md-6 text-end">
                                    <span class="badge 
                                        <?php echo $order['status'] === 'delivered' ? 'bg-success' : 
                                              ($order['status'] === 'cancelled' ? 'bg-danger' : 'bg-info'); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Order Date:</strong> <?php echo date('M j, Y h:i A', strtotime($order['created_at'])); ?></p>
                                    <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Shipping Address:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Order Items</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_items as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php echo htmlspecialchars($item['photo_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-thumbnail me-3" style="width: 60px; height: 60px;">
                                                        <div>
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                            <small class="text-muted">Product ID: <?php echo $item['product_id']; ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>৳<?php echo number_format($item['price'], 2); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td>৳<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                            <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Shipping Fee:</strong></td>
                                            <td>৳<?php echo number_format($order['shipping_fee'] ?? 0, 2); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td>৳<?php echo number_format($order['total_amount'] + ($order['shipping_fee'] ?? 0), 2); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="orders.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Orders
                        </a>
                        
                        <?php if ($order['status'] === 'pending'): ?>
                            <button class="btn btn-danger ms-2" id="cancel-order-btn" data-order-id="<?php echo $order_id; ?>">
                                <i class="fas fa-times me-2"></i>Cancel Order
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cancel order functionality
        document.getElementById('cancel-order-btn')?.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            
            if (confirm('Are you sure you want to cancel this order?')) {
                fetch('api/cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'order_id=' + orderId
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Order has been cancelled successfully.');
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to cancel order.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while cancelling the order.');
                });
            }
        });
    </script>
</body>
</html>