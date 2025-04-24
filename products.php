<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - FreshMart</title>
    <link rel="icon" type="image/png" href="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT3aAAKqYeAJ85UjxrgA4ZiQtpaDju-UTez55LckWFBFu9_VpSMWFClskEprIv-x8S-L3U&usqp=CAU">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
    <?php
    require_once 'includes/db_connection.php';
    require_once 'includes/functions.php';
    include 'includes/header.php';
    ?>
    </header>

    <!-- Main Content -->
    <main class="container mt-5">
        <?php
        
        // Get category from URL parameter
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Prepare SQL query based on parameters
        if (!empty($category)) {
            $sql = "SELECT * FROM products WHERE category = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$category]);
            $page_title = ucfirst($category);
        } elseif (!empty($search)) {
            $sql = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ?";
            $stmt = $pdo->prepare($sql);
            $search_term = "%$search%";
            $stmt->execute([$search_term, $search_term]);
            $page_title = "Search Results for '$search'";
        } else {
            $sql = "SELECT * FROM products";
            $stmt = $pdo->query($sql);
            $page_title = "All Products";
        }
        
        $products = [];
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <h2 class="mb-4"><?php echo $page_title; ?></h2>
        
        <?php if (empty($products)): ?>
            <div class="alert alert-info">No products found.</div>
        <?php else: ?>
            <div class="row" id="products-container">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card product-card">
                            <img src="<?php echo htmlspecialchars($product['photo_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                                <p class="product-price">৳ <?php echo number_format($product['price'] , 2); ?></p>
                                <div class="quantity-control mb-3">
                                    <label for="quantity-<?php echo $product['product_id']; ?>" class="form-label">Quantity:</label>
                                    <input type="number" class="form-control" id="quantity-<?php echo $product['product_id']; ?>" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                </div>
                                <a href="product-details.php?id=<?php echo $product['product_id']; ?>" class="btn btn-success">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script src="assets/js/products.js"></script>
</body>
</html>