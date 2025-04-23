<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db_connection.php';

// Check if current admin is super_admin
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$current_admin = $stmt->fetch();

if ($current_admin['role'] !== 'super_admin') {
    header('Location: admins.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($username)) $errors['username'] = 'Username is required';
    if (empty($full_name)) $errors['full_name'] = 'Full name is required';
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors['email'] = 'Email already exists';
    }

    if (empty($role)) $errors['role'] = 'Role is required';
    if (empty($password)) $errors['password'] = 'Password is required';
    if (strlen($password) < 8) $errors['password'] = 'Password must be at least 8 characters';
    if ($password !== $confirm_password) $errors['confirm_password'] = 'Passwords do not match';

    if (empty($errors)) {
        try {
            // Insert new admin with proper password hashing
            $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            
            $stmt = $pdo->prepare("INSERT INTO admins (username, full_name, email, role, password, created_at, last_login) 
                                  VALUES (?, ?, ?, ?, ?, NOW(), NULL)");
            $stmt->execute([$username, $full_name, $email, $role, $hashed_password]);

            $_SESSION['flash_message'] = 'Admin added successfully';
            $_SESSION['flash_type'] = 'success';
            header('Location: admins.php');
            exit;
        } catch (PDOException $e) {
            $errors['database'] = 'Error adding admin: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Admin - FreshMart</title>
    <link rel="icon" type="image/png" href="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT3aAAKqYeAJ85UjxrgA4ZiQtpaDju-UTez55LckWFBFu9_VpSMWFClskEprIv-x8S-L3U&usqp=CAU">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.5);
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: rgba(255,255,255,.75);
            background: rgba(255,255,255,.1);
        }
        .main-content {
            padding: 20px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
        }
        .password-strength {
            height: 5px;
            margin-top: 5px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-0">
                <div class="text-center py-3">
                    <h4 class="text-white">FreshMart Admin</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-boxes me-2"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-shopping-cart me-2"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customers.php">
                            <i class="fas fa-users me-2"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admins.php">
                            <i class="fas fa-user-shield me-2"></i> Admins
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-md-10 ms-auto main-content">
                <nav class="navbar navbar-light bg-light mb-4">
                    <div class="container-fluid">
                        <span class="navbar-brand"><?php echo $current_admin['full_name'] ?? 'Admin'; ?></span>
                        <div class="d-flex">
                            <span class="badge bg-primary me-3"><?php echo ucfirst(str_replace('_', ' ', $current_admin['role'])); ?></span>
                            <span class="text-muted">Last Login: <?php echo date('M j, Y g:i A', strtotime($current_admin['last_login'])); ?></span>
                        </div>
                    </div>
                </nav>

                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i> Add New Admin</h4>
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
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                                           id="username" name="username" required
                                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                                    <div class="invalid-feedback">
                                        Please provide a username.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>" 
                                           id="full_name" name="full_name" required
                                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                                    <div class="invalid-feedback">
                                        Please provide the full name.
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
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select <?php echo isset($errors['role']) ? 'is-invalid' : ''; ?>" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="super_admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'super_admin') ? 'selected' : ''; ?>>Super Admin</option>
                                    <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    <option value="editor" <?php echo (isset($_POST['role']) && $_POST['role'] === 'editor') ? 'selected' : ''; ?>>Editor</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a role.
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                           id="password" name="password" required minlength="8">
                                    <div class="password-strength mt-2">
                                        <div class="password-strength-bar" id="password-strength-bar"></div>
                                    </div>
                                    <small class="text-muted">Minimum 8 characters</small>
                                    <div class="invalid-feedback">
                                        Please provide a valid password (min 8 characters).
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                           id="confirm_password" name="confirm_password" required>
                                    <div class="invalid-feedback">
                                        Passwords must match.
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="admins.php" class="btn btn-secondary me-2">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i> Save Admin
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side validation
        (function () {
            'use strict'
            
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation')
            
            // Loop over them and prevent submission
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
        
        // Password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const feedback = this.nextElementSibling;
            
            if (password !== confirmPassword) {
                this.classList.add('is-invalid');
                feedback.textContent = 'Passwords do not match';
            } else {
                this.classList.remove('is-invalid');
            }
        });

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('password-strength-bar');
            let strength = 0;
            
            if (password.length >= 8) strength += 1;
            if (password.match(/[a-z]+/)) strength += 1;
            if (password.match(/[A-Z]+/)) strength += 1;
            if (password.match(/[0-9]+/)) strength += 1;
            if (password.match(/[$@#&!]+/)) strength += 1;
            
            // Update the strength bar
            const width = (strength / 5) * 100;
            strengthBar.style.width = width + '%';
            
            // Change color based on strength
            if (strength < 2) {
                strengthBar.style.backgroundColor = '#dc3545';
            } else if (strength < 4) {
                strengthBar.style.backgroundColor = '#ffc107';
            } else {
                strengthBar.style.backgroundColor = '#28a745';
            }
        });
    </script>
</body>
</html>