<?php
// Start session (if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "grocery_shop";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details from database
$product = null;
if ($product_id > 0) {
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
    } catch (Exception $e) {
        // Handle database error
        $error = "Database error: " . $e->getMessage();
    }
}

// Set page title
$page_title = $product ? htmlspecialchars($product['name']) . ' - FreshMart' : 'Product Not Found - FreshMart';
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
    <!-- Header -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <img src="assets/images/grocery store logo.png" alt="FreshMart Logo" height="40">
                    FreshMart
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Categories
                            </a>
                            <ul class="dropdown-menu" id="category-dropdown">
                                <li><a class="dropdown-item" href="products.php?category=fruits">Fruits</a></li>
                                <li><a class="dropdown-item" href="products.php?category=vegetables">Vegetables</a></li>
                                <li><a class="dropdown-item" href="products.php?category=dairies">Dairy</a></li>
                                <li><a class="dropdown-item" href="products.php?category=beverages">Beverages</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">Cart <span id="cart-count" class="badge bg-success"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span></a>
                        </li>
                    </ul>
                    <div class="d-flex">
                        <form class="d-flex me-2" method="GET" action="products.php">
                            <input class="form-control me-2" type="search" name="search" placeholder="Search products..." aria-label="Search">
                            <button class="btn btn-outline-success" type="submit">Search</button>
                        </form>
                        <div id="auth-buttons">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                        <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                                        <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-outline-primary me-2">Login</a>
                                <a href="register.php" class="btn btn-primary">Register</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container mt-5">
        <div id="product-details">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php elseif (!$product): ?>
                <div class="alert alert-warning">Product not found.</div>
                <div class="text-center mt-4">
                    <a href="products.php" class="btn btn-primary">Browse Products</a>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-md-6">
                        <img src="<?php echo htmlspecialchars($product['photo_url']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    <div class="col-md-6">
                        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                        <p class="text-muted">Category: <?php echo ucfirst(htmlspecialchars($product['category'])); ?></p>
                        <div class="mb-3">
                            <span class="h4 text-success">৳ <?php echo number_format($product['price'] * 10, 2); ?></span>
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <span class="badge bg-success ms-2">In Stock</span>
                            <?php else: ?>
                                <span class="badge bg-danger ms-2">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <form class="mt-4" id="add-to-cart-form">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="quantity" class="form-label">Quantity:</label>
                                        <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                    </div>
                                </div>
                                <input type="hidden" id="product-id" value="<?php echo $product['product_id']; ?>">
                                <input type="hidden" id="product-name" value="<?php echo htmlspecialchars($product['name']); ?>">
                                <input type="hidden" id="product-price" value="<?php echo number_format($product['price'] * 10 * 10, 2); ?>">
                                <input type="hidden" id="product-image" value="<?php echo htmlspecialchars($product['photo_url']); ?>">
                                
                                <button type="button" class="btn btn-primary btn-lg" id="add-to-cart-btn">
                                    <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning mt-4">This product is currently out of stock.</div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <h5>Product Details</h5>
                            <ul>
                                <li>Category: <?php echo ucfirst(htmlspecialchars($product['category'])); ?></li>
                                <li>Available Quantity: <?php echo $product['stock_quantity']; ?></li>
                                <li>Price: ৳<?php echo number_format($product['price'] * 10 * 10, 2); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>FreshMart</h5>
                    <p>Your one-stop shop for fresh groceries and household essentials.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Home</a></li>
                        <li><a href="products.php" class="text-white">Products</a></li>
                        <li><a href="cart.php" class="text-white">Cart</a></li>
                        <li><a href="login.php" class="text-white">Login</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact Us</h5>
                    <address>
                        <p>Daffodil Smart City<br>
                        Savar, Asulia<br>
                        Phone: +8801*********<br>
                        Email: info@freshmart.com</p>
                    </address>
                </div>
                <div class="col-md-2">
                    <h5>Follow Us</h5>
                    <div class="social-icons">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12 text-center">
                    <p>&copy; 2025 FreshMart. All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    <?php
    $conn->close();
    ?>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Add to cart functionality
        document.getElementById('add-to-cart-btn')?.addEventListener('click', function() {
            const productId = document.getElementById('product-id').value;
            const productName = document.getElementById('product-name').value;
            const productPrice = document.getElementById('product-price').value;
            const productImage = document.getElementById('product-image').value;
            const quantity = document.getElementById('quantity').value;
            
            // Send AJAX request to add to cart
            axios.post('api/add_to_cart.php', {
                product_id: productId,
                name: productName,
                price: productPrice,
                image: productImage,
                quantity: quantity
            })
            .then(response => {
                if (response.data.success) {
                    // Update cart count
                    const cartCount = document.getElementById('cart-count');
                    if (cartCount) {
                        cartCount.textContent = response.data.cart_count;
                    }
                    
                    // Show success message
                    alert('Product added to cart successfully!');
                } else {
                    alert('Error: ' + response.data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding to cart.');
            });
        });
    </script>
    
<script>
document.getElementById('add-to-cart-btn')?.addEventListener('click', function() {
    const productId = document.getElementById('product-id').value;
    const productName = document.getElementById('product-name').value;
    const productPrice = document.getElementById('product-price').value;
    const productImage = document.getElementById('product-image').value;
    const quantity = document.getElementById('quantity').value;
    
    fetch('api/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            name: productName,
            price: productPrice,
            image: productImage,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                cartCount.textContent = data.cart_count;
            }
            
            // Show success message
            alert('Go to cart');
        } else {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                alert('Error: ' + data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding to cart.');
    });
});
</script>

</body>
</html>