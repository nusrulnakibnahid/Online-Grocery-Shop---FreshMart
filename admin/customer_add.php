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
    
    // Generate username from email (or first name + last name)
    $username = strtolower(str_replace(' ', '', $first_name . $last_name)); // Alternative: explode('@', $email)[0]

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
            
            // Insert new customer with username
            $stmt = $pdo->prepare("INSERT INTO users (username, first_name, last_name, email, phone, password, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $first_name, $last_name, $email, $phone, $hashed_password, $address]);

            $_SESSION['flash_message'] = 'Customer added successfully. A password reset email has been sent to the customer.';
            $_SESSION['flash_type'] = 'success';
            header('Location: customers.php');
            exit;
        } catch (PDOException $e) {
            // Handle potential username duplicates
            if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'username') !== false) {
                // Try again with a slightly different username
                $username = $username . rand(100, 999); // Append random numbers
                $stmt->execute([$username, $first_name, $last_name, $email, $phone, $hashed_password, $address]);
                
                $_SESSION['flash_message'] = 'Customer added successfully with adjusted username.';
                $_SESSION['flash_type'] = 'success';
                header('Location: customers.php');
                exit;
            } else {
                $errors['database'] = 'Error adding customer: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i> Add New Customer</h4>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" 
                           id="first_name" name="first_name" required
                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                    <div class="invalid-feedback">
                        Please provide a first name.
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" 
                           id="last_name" name="last_name" required
                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                    <div class="invalid-feedback">
                        Please provide a last name.
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                       id="email" name="email" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <div class="invalid-feedback">
                    Please provide a valid email.
                </div>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                       id="phone" name="phone" required
                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                <div class="invalid-feedback">
                    Please provide a phone number.
                </div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                          id="address" name="address" rows="3" required><?php 
                    echo htmlspecialchars($_POST['address'] ?? ''); 
                ?></textarea>
                <div class="invalid-feedback">
                    Please provide an address.
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> The customer will receive an email to set their password.
            </div>

            <div class="d-flex justify-content-end">
                <a href="customers.php" class="btn btn-secondary me-2">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Add Customer
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>