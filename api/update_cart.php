<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to update cart',
        'redirect' => 'auth/login.php'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id'], $input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (!isset($_SESSION['cart']) || !isset($_SESSION['cart'][$input['product_id']])) {
    echo json_encode(['success' => false, 'message' => 'Product not found in cart']);
    exit;
}

$_SESSION['cart'][$input['product_id']]['quantity'] = (int)$input['quantity'];

echo json_encode([
    'success' => true,
    'cart_count' => count($_SESSION['cart'])
]);
?>