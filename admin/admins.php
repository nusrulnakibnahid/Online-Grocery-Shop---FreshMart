<?php 
include 'includes/header.php';

if ($_SESSION['admin_role'] !== 'super_admin') {
    header('Location: index.php');
    exit;
}

if (isset($_GET['delete'])) {
    $admin_id = intval($_GET['delete']);
    
    if ($admin_id === $_SESSION['admin_id']) {
        $_SESSION['flash_message'] = 'You cannot delete your own account';
        $_SESSION['flash_type'] = 'danger';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->execute([$admin_id]);
            
            $_SESSION['flash_message'] = 'Admin deleted successfully';
            $_SESSION['flash_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = 'Error deleting admin: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }
    }
    
    header('Location: admins.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM admins ORDER BY created_at DESC");
$admins = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4><i class="fas fa-user-shield"></i> Admin Management</h4>
        <a href="admin_add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Admin
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
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><?php echo $admin['id']; ?></td>
                        <td><?php echo htmlspecialchars($admin['username']); ?></td>
                        <td><?php echo htmlspecialchars($admin['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($admin['email']); ?></td>
                        <td>
                            <span class="badge 
                                <?php echo $admin['role'] === 'super_admin' ? 'bg-danger' : 
                                      ($admin['role'] === 'admin' ? 'bg-primary' : 'bg-info'); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo $admin['last_login'] ? date('M j, Y g:i A', strtotime($admin['last_login'])) : 'Never'; ?>
                        </td>
                        <td>
                            <a href="admin_edit.php?id=<?php echo $admin['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($admin['role'] !== 'super_admin'): ?>
                                <a href="admins.php?delete=<?php echo $admin['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this admin?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>