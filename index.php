<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreshMart - Online Grocery Shop</title>
    <link rel="icon" type="image/png" href="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT3aAAKqYeAJ85UjxrgA4ZiQtpaDju-UTez55LckWFBFu9_VpSMWFClskEprIv-x8S-L3U&usqp=CAU">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
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

    <main>
        <section class="hero-section">
            <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="assets/images/banner1.jpg" class="d-block w-100" alt="Fresh Groceries">
                        <div class="carousel-caption d-none d-md-block">
                            <h1>Fresh Groceries Delivered to Your Doorstep</h1>
                            <p>তাজা ও অর্গানিক পণ্য সহজ মূল্যে এখন হাতের নাগালে!</p>
                            <a href="products.php" class="btn btn-success btn-lg">Shop Now</a>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="assets/images/banner2.jpg" class="d-block w-100" alt="Special Offers">
                        <div class="carousel-caption d-none d-md-block">
                            <h1>Special Offers Every Day</h1>
                            <p>প্রিয় পণ্যে সাশ্রয় করুন আরও বেশি!</p>
                            <a href="products.php" class="btn btn-success btn-lg">View Offers</a>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </section>

        <section class="container mt-5">
            <h2 class="text-center mb-4">Shop by Category</h2>
            <div class="row" id="featured-categories">
                <?php
                $categories = [
                    'Fruits' => 'assets/images/Fruits/Fruits.jpg',
                    'Vegetables' => 'assets/images/Vegetables/Vegetables.jpg',
                    'Dairies' => 'assets/images/Dairies/Dairy.jpg',
                    'Bakery' => 'assets/images/Bakery/Bakery.jpeg',
                    'Beverages' => 'assets/images/Beverages/Beverages.png'
                ];
                
                foreach ($categories as $category => $image) {
                    echo '<div class="col-md-4 mb-4">
                            <div class="card category-card">
                                <img src="' . $image . '" class="card-img-top" alt="' . $category . '">
                                <div class="card-body text-center">
                                    <h5 class="card-title">' . $category . '</h5>
                                    <a href="products.php?category=' . strtolower($category) . '" class="btn btn-success">Shop Now</a>
                                </div>
                            </div>
                        </div>';
                }
                ?>
            </div>
        </section>

        <section class="container mt-5">
            <h2 class="text-center mb-4">Featured Products</h2>
            <div class="row" id="featured-products">
                <?php
                $sql = "SELECT * FROM products ORDER BY RAND() LIMIT 6";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $result = [];
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($result) > 0) {
                    $counter = 1;
                    foreach ($result as $row) {
                        $price_in_taka = $row['price'] * 10;
                        
                        echo '<div class="col-md-4 mb-4">
                                <div class="card product-card">
                                    <img src="' . htmlspecialchars($row['photo_url']) . '" class="card-img-top" alt="' . htmlspecialchars($row['name']) . '">
                                    <div class="card-body">
                                        <h5 class="card-title">' . htmlspecialchars($row['name']) . '</h5>
                                        <p class="card-text">' . htmlspecialchars($row['description']) . '</p>
                                        <p class="product-price">৳' . number_format($price_in_taka, 0) . '/unit</p>
                                        <a href="product-details.php?id=' . htmlspecialchars($row['product_id']) . '" class="btn btn-primary">View Details</a>
                                    </div>
                                </div>
                            </div>';
                        $counter++;
                    }
                } else {
                    echo '<div class="col-12 text-center"><p>No products found.</p></div>';
                }
                ?>
            </div>
        </section>

        <section class="container mt-5">
            <h2 class="text-center mb-4">Special Offers</h2>
            <div class="row" id="special-offers">
                <?php
                $sql = "SELECT * FROM products ORDER BY RAND() LIMIT 6";
                $result = $pdo->query($sql);

                if ($result->rowCount() > 0) {
                    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        $discount = rand(10, 25);
                        $original_price = $row['price'] * 10;
                        $discounted_price = $original_price * (1 - ($discount/100));
                        
                        echo '<div class="col-md-4 mb-4">
                                <div class="card product-card border-success">
                                    <img src="' . $row['photo_url'] . '" class="card-img-top" alt="' . $row['name'] . '">
                                    <div class="card-body">
                                        <h5 class="card-title">' . $row['name'] . '</h5>
                                        <p class="card-text">' . $row['description'] . '</p>
                                        <p class="product-price text-success">৳' . number_format($discounted_price, 0) . ' <small class="text-muted">(' . $discount . '% Off)</small></p>
                                        <a href="product-details.php?id=' . $row['product_id'] . '" class="btn btn-success">Buy Now</a>
                                    </div>
                                </div>
                            </div>';
                    }
                } else {
                    echo '<div class="col-12 text-center"><p>No special offers available.</p></div>';
                }
                ?>
            </div>
        </section>

        <section class="container-fluid bg-light py-5 mt-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 text-center">
                        <h3>Subscribe to Our Newsletter</h3>
                        <p>Get updates on new products and special offers</p>
                        <form class="row g-3 justify-content-center" method="post" action="subscribe.php">
                            <div class="col-auto">
                                <input type="email" class="form-control" name="newsletter-email" id="newsletter-email" placeholder="Your Email" required>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-success mb-3">Subscribe</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="assets/js/auth.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>