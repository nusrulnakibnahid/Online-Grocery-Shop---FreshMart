<?php
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get order ID from POST data
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

if ($order_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    // Begin transaction
    $pdo->beginTransaction();
    
    // 1. Verify the order belongs to the user and is pending
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'pending' FOR UPDATE");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        $pdo->rollBack();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Order not found, already processed, or you dont have permission']);
        exit;
    }
    
    // 2. Update order status to cancelled
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$order_id]);
    
    // 3. Restore product quantities (if needed)
    $stmt = $pdo->prepare("
        SELECT product_id, quantity 
        FROM order_items 
        WHERE order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();
    
    foreach ($items as $item) {
        $stmt = $pdo->prepare("
            UPDATE products 
            SET stock_quantity = stock_quantity + ? 
            WHERE product_id = ?
        ");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>