<?php 
include 'includes/header.php';

if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    
    try {
        $stmt = $pdo->prepare("SELECT photo_url FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if ($product && !empty($product['photo_url'])) {
            $image_path = '../../' . $product['photo_url'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        $_SESSION['flash_message'] = 'Product deleted successfully';
        $_SESSION['flash_type'] = 'success';
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = 'Error deleting product: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'danger';
    }
    
    header('Location: products.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4><i class="fas fa-boxes"></i> Product Management</h4>
        <a href="product_add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Product
        </a>
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
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['product_id']; ?></td>
                        <td>
                            <?php if (!empty($product['photo_url'])): ?>
                                <img src="../<?php echo htmlspecialchars($product['photo_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light text-center" style="width: 50px; height: 50px; line-height: 50px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo ucfirst(htmlspecialchars($product['category'])); ?></td>
                        <td>৳<?php echo number_format($product['price'], 2); ?></td>
                        <td class="<?php echo $product['stock_quantity'] <= 0 ? 'text-danger' : ''; ?>">
                            <?php echo $product['stock_quantity']; ?>
                        </td>
                        <td>
                            <span class="badge <?php echo $product['stock_quantity'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="product_edit.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="products.php?delete=<?php echo $product['product_id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this product?')">
                                <i class="fas fa-trash"></i>
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