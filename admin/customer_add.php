<?php
include 'includes/header.php';

// Check admin permissions
if ($_SESSION['admin_role'] !== 'super_admin' && $_SESSION['admin_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Validation
    if (empty($first_name)) $errors['first_name'] = 'First name is required';
    if (empty($last_name)) $errors['last_name'] = 'Last name is required';
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors['email'] = 'Email already exists';
    }

    if (empty($phone)) $errors['phone'] = 'Phone number is required';

    if (empty($errors)) {
        try {
            // Generate a random password (not shown to admin)
            $random_password = bin2hex(random_bytes(8));
            $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
            
            // Insert new customer
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, password, address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $email, $phone, $hashed_password, $address]);

            $_SESSION['flash_message'] = 'Customer added successfully. A password reset email has been sent to the customer.';
            $_SESSION['flash_type'] = 'success';
            header('Location: customers.php');
            exit;
        } catch (PDOException $e) {
            $errors['database'] = 'Error adding customer: ' . $e->getMessage();
        }
    }
}
?>

<div class="card">
    <div class="card-header">
        <h4><i class="fas fa-user-plus"></i> Add New Customer</h4>
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

        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" 
                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" 
                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="tel" class="form-control" id="phone" name="phone" 
                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3"><?php 
                    echo htmlspecialchars($_POST['address'] ?? ''); 
                ?></textarea>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> The customer will receive an email to set their password.
            </div>

            <button type="submit" class="btn btn-primary">Add Customer</button>
            <a href="customers.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>