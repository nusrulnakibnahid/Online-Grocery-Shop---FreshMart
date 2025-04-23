<?php 
include 'includes/header.php';

// Only allow admins to access this page
if ($_SESSION['admin_role'] !== 'super_admin' && $_SESSION['admin_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Handle customer deletion
if (isset($_GET['delete'])) {
    $customer_id = intval($_GET['delete']);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$customer_id]);
        
        $_SESSION['flash_message'] = 'Customer deleted successfully';
        $_SESSION['flash_type'] = 'success';
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = 'Error deleting customer: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'danger';
    }
    
    header('Location: customers.php');
    exit;
}

// Fetch all customers
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$customers = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4><i class="fas fa-users"></i> Customer Management</h4>
        <a href="customer_add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Customer
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash_type']; ?>">
                <?php echo $_SESSION['flash_message']; ?>
                <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
            </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-striped data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registered On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo $customer['id']; ?></td>
                        <td><?php echo htmlspecialchars($customer['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($customer['created_at'])); ?></td>
                        <td>
                            <a href="customer_edit.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="customers.php?delete=<?php echo $customer['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this customer?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>