<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
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
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <?php 
                    if (basename($_SERVER['PHP_SELF']) === 'index.php') {?>
                    <a class="navbar-brand" href="index.php">
                        <img src="assets/images/grocery store logo.png" alt="FreshMart Logo" height="40">FreshMart</a>
                        <?php } else {?>
                            <a class="navbar-brand" href="index.php">
                            <img src="assets/images/grocery store logo.png" alt="FreshMart Logo" height="40">FreshMart</a>
                        <?php }
                    ?>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Categories
                            </a>
                            <ul class="dropdown-menu" id="category-dropdown">
                                <?php
                                $sql = "SELECT DISTINCT category FROM products ORDER BY category";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute();
                                
                                $icons = [
                                    'Fruits' => 'fas fa-apple-alt',
                                    'Vegetables' => 'fas fa-carrot',
                                    'Dairies' => 'fas fa-cheese',
                                    'Beverages' => 'fas fa-coffee',
                                    'Bakery' => 'fa-solid fa-bread-slice'
                                ];
                                
                                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($categories as $row) {
                                    $category = $row['category'];
                                    $icon = isset($icons[$category]) ? $icons[$category] : 'fas fa-tag';
                                        echo '<li>
                                                <a class="dropdown-item" href="products.php?category=' . strtolower($category) . '">
                                                    <i class="' . $icon . ' me-2"></i>&nbsp;&nbsp;' . $category . '
                                                </a>
                                            </li>';
                                }
                                ?>
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

    <main class="container mt-5">
        <?php
        // Display flash messages
        if (isset($_SESSION['flash_message'])) {
            echo '<div class="alert alert-' . $_SESSION['flash_type'] . '">' . $_SESSION['flash_message'] . '</div>';
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
        }
        ?>