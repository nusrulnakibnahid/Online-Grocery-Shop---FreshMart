<?php
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$cart_items = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {

    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    
    $stmt = $pdo->prepare("SELECT product_id, name, price, photo_url, stock_quantity FROM products WHERE product_id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        $cart_item = $_SESSION['cart'][$product['product_id']];
        $cart_items[] = [
            'id' => $product['product_id'],
            'name' => $product['name'],
            'price' => $product['price'] ,
            'image' => $product['photo_url'],
            'quantity' => $cart_item['quantity'],
            'stock' => $product['stock_quantity'],
            'subtotal' => $product['price']  * $cart_item['quantity']
        ];
        $total += $product['price']  * $cart_item['quantity'] ;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - FreshMart</title>
    <link rel="icon" type="image/png" href="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT3aAAKqYeAJ85UjxrgA4ZiQtpaDju-UTez55LckWFBFu9_VpSMWFClskEprIv-x8S-L3U&usqp=CAU">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-5 mb-5">
        <h2 class="mb-4">Your Shopping Cart</h2>
        
        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info">
                Your cart is empty. <a href="products.php">Continue shopping</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cart_items as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                             class="img-thumbnail me-3" width="80">
                                                        <div>
                                                            <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                                            <?php if ($item['quantity'] > $item['stock']): ?>
                                                                <span class="text-danger">Only <?php echo $item['stock']; ?> available</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>৳ <?php echo number_format($item['price'], 2); ?></td>
                                                <td>
                                                    <div class="input-group" style="width: 120px;">
                                                        <button class="btn btn-outline-secondary update-quantity" 
                                                                type="button" 
                                                                data-product-id="<?php echo $item['id']; ?>" 
                                                                data-action="decrease">-</button>
                                                        <input type="number" class="form-control text-center quantity-input" 
                                                               value="<?php echo $item['quantity']; ?>" 
                                                               min="1" max="<?php echo $item['stock']; ?>"
                                                               data-product-id="<?php echo $item['id']; ?>">
                                                        <button class="btn btn-outline-secondary update-quantity" 
                                                                type="button" 
                                                                data-product-id="<?php echo $item['id']; ?>" 
                                                                data-action="increase">+</button>
                                                    </div>
                                                </td>
                                                <td>৳ <?php echo number_format($item['subtotal'], 2); ?></td>
                                                <td>
                                                    <button class="btn btn-danger btn-sm remove-item" 
                                                            data-product-id="<?php echo $item['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Order Summary</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>৳<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span>৳50.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Tax:</span>
                                <span>৳<?php echo number_format($total * 0.1, 2); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold mb-4">
                                <span>Total:</span>
                                <span>৳<?php echo number_format($total + 5 + ($total * 0.1), 2); ?></span>
                            </div>
                            <a href="checkout.php" class="btn btn-primary w-1100">Proceed to Checkout</a>
                            <a href="products.php" class="btn btn-outline-secondary w-1100 mt-2">Continue Shopping</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.update-quantity').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const action = this.dataset.action;
                const input = this.parentElement.querySelector('.quantity-input');
                let quantity = parseInt(input.value);
                
                if (action === 'increase') {
                    quantity++;
                } else if (action === 'decrease' && quantity > 1) {
                    quantity--;
                }
                
                input.value = quantity;
                updateCartItem(productId, quantity);
            });
        });
        
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const productId = this.dataset.productId;
                const quantity = parseInt(this.value);
                
                if (quantity >= 1) {
                    updateCartItem(productId, quantity);
                }
            });
        });
        
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                removeCartItem(productId);
            });
        });
        
        function updateCartItem(productId, quantity) {
            fetch('api/update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating cart.');
            });
        }
        
        function removeCartItem(productId) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                fetch('api/remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                      
                        const cartCount = document.getElementById('cart-count');
                        if (cartCount) {
                            cartCount.textContent = data.cart_count;
                        }
                      
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing item.');
                });
            }
        }
    </script>
</body>
</html>