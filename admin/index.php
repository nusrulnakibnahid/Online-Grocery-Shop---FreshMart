<?php include 'includes/header.php'; ?>

<div class="card mb-4">
    <div class="card-header">
        <h4><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Products</h5>
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM products");
                        $product_count = $stmt->fetchColumn();
                        ?>
                        <h2><?php echo $product_count; ?></h2>
                        <a href="products.php" class="text-white">View Products</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Orders</h5>
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
                        $order_count = $stmt->fetchColumn();
                        ?>
                        <h2><?php echo $order_count; ?></h2>
                        <a href="orders.php" class="text-white">View Orders</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Customers</h5>
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                        $user_count = $stmt->fetchColumn();
                        ?>
                        <h2><?php echo $user_count; ?></h2>
                        <a href="customers.php" class="text-white">View Customers</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h5 class="card-title">Revenue</h5>
                        <?php
                        $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'delivered'");
                        $revenue = $stmt->fetchColumn();
                        ?>
                        <h2>৳<?php echo number_format($revenue ?? 0, 2); ?></h2>
                        <a href="orders.php" class="text-dark">View Sales</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Orders</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->query("SELECT o.id, u.username, o.total_amount, o.status 
                                                         FROM orders o JOIN users u ON o.user_id = u.id 
                                                         ORDER BY o.created_at DESC LIMIT 5");
                                    while ($order = $stmt->fetch()):
                                    ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                                        <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="badge 
                                                <?php echo $order['status'] === 'delivered' ? 'bg-success' : 
                                                      ($order['status'] === 'cancelled' ? 'bg-danger' : 'bg-info'); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Low Stock Products</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Stock</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->query("SELECT product_id, name, stock_quantity, price 
                                                         FROM products WHERE stock_quantity < 10 ORDER BY stock_quantity ASC LIMIT 5");
                                    while ($product = $stmt->fetch()):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td class="<?php echo $product['stock_quantity'] < 5 ? 'text-danger fw-bold' : 'text-warning'; ?>">
                                            <?php echo $product['stock_quantity']; ?>
                                        </td>
                                        <td>৳<?php echo number_format($product['price'], 2); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>