<?php 
include 'includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT o.*, u.username, u.email, u.phone, u.address 
                       FROM orders o JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

$stmt = $pdo->prepare("SELECT oi.*, p.name, p.photo_url 
                       FROM order_items oi JOIN products p ON oi.product_id = p.product_id 
                       WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4><i class="fas fa-receipt"></i> Order Details - #<?php echo $order['id']; ?></h4>
        <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5>Customer Information</h5>
                <table class="table table-bordered">
                    <tr>
                        <th>Name</th>
                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?php echo htmlspecialchars($order['email']); ?></td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td><?php echo htmlspecialchars($order['phone']); ?></td>
                    </tr>
                    <tr>
                        <th>Shipping Address</th>
                        <td><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5>Order Information</h5>
                <table class="table table-bordered">
                    <tr>
                        <th>Order Date</th>
                        <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge 
                                <?php echo $order['status'] === 'delivered' ? 'bg-success' : 
                                      ($order['status'] === 'cancelled' ? 'bg-danger' : 'bg-info'); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Payment Method</th>
                        <td><?php echo strtoupper($order['payment_method']); ?></td>
                    </tr>
                    <tr>
                        <th>Total Amount</th>
                        <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <h5 class="mt-4">Order Items</h5>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if (!empty($item['photo_url'])): ?>
                                    <img src="../<?php echo htmlspecialchars($item['photo_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         style="width: 50px; height: 50px; object-fit: cover;" class="me-3">
                                <?php endif; ?>
                                <div><?php echo htmlspecialchars($item['name']); ?></div>
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
                        <th colspan="3" class="text-end">Subtotal:</th>
                        <td>৳<?php echo number_format($order['total_amount'] - 5 - ($order['total_amount'] * 0.1), 2); ?></td>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-end">Shipping:</th>
                        <td>$5.00</td>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-end">Tax (10%):</th>
                        <td>৳<?php echo number_format($order['total_amount'] * 0.1, 2); ?></td>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-end">Total:</th>
                        <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if ($_SESSION['admin_role'] === 'super_admin' && in_array($order['status'], ['pending', 'processing'])): ?>
            <div class="text-end mt-3">
                <form method="POST" action="orders.php" class="d-inline">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <input type="hidden" name="status" value="cancelled">
                    <input type="hidden" name="update_status" value="1">
                    <button type="submit" class="btn btn-danger" 
                            onclick="return confirm('Are you sure you want to cancel this order?')">
                        <i class="fas fa-times"></i> Cancel Order
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>