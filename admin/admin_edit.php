<?php 
include 'includes/header.php';

if ($_SESSION['admin_role'] !== 'super_admin') {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: admins.php');
    exit;
}

$admin_id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

if (!$admin) {
    header('Location: admins.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin['username'] = trim($_POST['username']);
    $admin['email'] = trim($_POST['email']);
    $admin['full_name'] = trim($_POST['full_name']);
    $admin['role'] = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$admin['username'], $admin['email'], $admin_id]);
    if ($stmt->fetch()) {
        $errors['username'] = 'Username or email already exists';
    }
    
    if (empty($errors)) {
        try {
            $query = "UPDATE admins SET username = ?, email = ?, full_name = ?, role = ?";
            $params = [
                $admin['username'],
                $admin['email'],
                $admin['full_name'],
                $admin['role']
            ];
            
            if (!empty($password)) {
                $query .= ", password = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }
            
            $query .= " WHERE id = ?";
            $params[] = $admin_id;
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            
            $_SESSION['flash_message'] = 'Admin updated successfully';
            $_SESSION['flash_type'] = 'success';
            header('Location: admins.php');
            exit;
        } catch (PDOException $e) {
            $errors['database'] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<div class="card">
    <div class="card-header">
        <h4><i class="fas fa-user-edit"></i> Edit Admin</h4>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="admin_edit.php?id=<?php echo $admin_id; ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" 
                       value="<?php echo htmlspecialchars($admin['full_name']); ?>">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                </div>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="admin" <?php echo $admin['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="manager" <?php echo $admin['role'] === 'manager' ? 'selected' : ''; ?>>Manager</option>
                    <option value="super_admin" <?php echo $admin['role'] === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Admin</button>
            <a href="admins.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>