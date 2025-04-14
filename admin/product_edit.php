<?php 
include 'includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$product_id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product['name'] = trim($_POST['name']);
    $product['description'] = trim($_POST['description']);
    $product['price'] = floatval($_POST['price']);
    $product['category'] = trim($_POST['category']);
    $product['stock_quantity'] = intval($_POST['stock_quantity']);
    

    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/'.$product['category'].'/' ;
        $file_name = time() . '_' . basename($_FILES['image']['name']);
        $target_path = $upload_dir . $file_name;
        
        $imageFileType = strtolower(pathinfo($target_path, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($imageFileType, $allowed_types)) {
            $errors['image'] = 'Only JPG, JPEG, PNG, GIF & WEBP files are allowed';
        } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            if (!empty($product['photo_url'])) {
                $old_image_path = '../' . $product['photo_url'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
            $product['photo_url'] = 'assets/images/'.$product['category'].'/'. $file_name;
        } else {
            $errors['image'] = 'Error uploading file';
        }
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE products SET 
                                  name = ?, description = ?, price = ?, category = ?, 
                                  stock_quantity = ?, photo_url = COALESCE(?, photo_url)
                                  WHERE product_id = ?");
            $stmt->execute([
                $product['name'],
                $product['description'],
                $product['price'],
                $product['category'],
                $product['stock_quantity'],
                $product['photo_url'] ?? null,
                $product_id
            ]);
            
            $_SESSION['flash_message'] = 'Product updated successfully';
            $_SESSION['flash_type'] = 'success';
            header('Location: products.php');
            exit;
        } catch (PDOException $e) {
            $errors['database'] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<div class="card">
    <div class="card-header">
        <h4><i class="fas fa-edit"></i> Edit Product</h4>
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
        
        <form method="POST" action="product_edit.php?id=<?php echo $product_id; ?>" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?php 
                    echo htmlspecialchars($product['description']); 
                ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="price" class="form-label">Price</label>
                    <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" 
                               value="<?php echo htmlspecialchars($product['price']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="stock_quantity" class="form-label">Stock Quantity</label>
                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                           value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="fruits" <?php echo $product['category'] === 'fruits' ? 'selected' : ''; ?>>Fruits</option>
                    <option value="vegetables" <?php echo $product['category'] === 'vegetables' ? 'selected' : ''; ?>>Vegetables</option>
                    <option value="dairies" <?php echo $product['category'] === 'dairies' ? 'selected' : ''; ?>>Dairy</option>
                    <option value="beverages" <?php echo $product['category'] === 'beverages' ? 'selected' : ''; ?>>Beverages</option>
                    <option value="bakery" <?php echo $product['category'] === 'bakery' ? 'selected' : ''; ?>>Bakery</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Product Image</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <?php if (!empty($product['photo_url'])): ?>
                    <div class="mt-2">
                        <img src="../<?php echo htmlspecialchars($product['photo_url']); ?>" 
                             alt="Current Product Image" style="max-height: 100px;">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image">
                            <label class="form-check-label" for="remove_image">Remove current image</label>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="products.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>