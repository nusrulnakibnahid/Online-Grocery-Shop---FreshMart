<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to add items to cart',
        'redirect' => 'login.php'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id'], $input['name'], $input['price'], $input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$product_id = $input['product_id'];
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['quantity'] += (int)$input['quantity'];
} else {
    $_SESSION['cart'][$product_id] = [
        'name' => $input['name'],
        'price' => (float)$input['price'],
        'image' => $input['image'] ?? '',
        'quantity' => (int)$input['quantity']
    ];
}

echo json_encode([
    'success' => true,
    'cart_count' => count($_SESSION['cart'])
]);