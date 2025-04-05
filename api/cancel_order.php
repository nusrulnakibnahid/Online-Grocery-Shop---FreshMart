<?php
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to cancel order']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing order ID']);
    exit;
}

$order_id = intval($input['order_id']);

try {
    // Check if order belongs to user and is cancellable
    $stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    if (!in_array($order['status'], ['pending', 'processing'])) {
        echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled at this stage']);
        exit;
    }

    // Update order status
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$order_id]);

    // Return success response
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>