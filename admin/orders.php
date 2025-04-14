<?php 
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        
        $_SESSION['flash_message'] = 'Order status updated successfully';
        $_SESSION['flash_type'] = 'success';
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = 'Error updating order status: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'danger';
    }
    
    header('Location: orders.php');
    exit;
}

$stmt = $pdo->query("SELECT o.*, u.username, u.email 
                     FROM orders o JOIN users u ON o.user_id = u.id 
                     ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h4><i class="fas fa-shopping-cart"></i> Order Management</h4>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash_type']; ?>">
                <?php echo $_SESSION['flash_message']; ?>
                <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
            </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-striped data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td>
                            <?php echo htmlspecialchars($order['username']); ?><br>
                            <small><?php echo htmlspecialchars($order['email']); ?></small>
                        </td>
                        <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                        <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" class="form-select form-select-sm" 
                                        onchange="this.form.submit()" <?php echo $_SESSION['admin_role'] !== 'super_admin' ? 'disabled' : ''; ?>>
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                        <td><?php echo strtoupper($order['payment_method']); ?></td>
                        <td>
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>