<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/User.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = sanitize($_POST['address'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');

    // Validation
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    if (empty($errors)) {
        $userModel = new User();
        
        // Check if email exists
        if ($userModel->isEmailRegistered($email)) {
            $errors['email'] = 'Email already registered';
        } else {
            // Register user
            $data = [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'address' => $address,
                'phone' => $phone
            ];
            
            if ($userModel->register($data)) {
                $success = 'Registration successful! Please login.';
                // Clear form
                $name = $email = $address = $phone = '';
            } else {
                $errors['general'] = 'Registration failed. Please try again.';
            }
        }
    }
}

// If user is already logged in, redirect to home
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title text-center">Register</h2>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" novalidate>
    <!-- Name Field -->
    <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
               id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>">
        <?php if (isset($errors['name'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['name']); ?></div>
        <?php endif; ?>
    </div>
    
    <!-- Email Field -->
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
               id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
        <?php if (isset($errors['email'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
        <?php endif; ?>
    </div>
    
    <!-- Password Field -->
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
               id="password" name="password">
        <?php if (isset($errors['password'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
        <?php endif; ?>
    </div>
    
    <!-- Confirm Password Field -->
    <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
               id="confirm_password" name="confirm_password">
        <?php if (isset($errors['confirm_password'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
        <?php endif; ?>
    </div>
    
    <!-- Address Field -->
    <div class="mb-3">
        <label for="address" class="form-label">Address (Optional)</label>
        <textarea class="form-control" id="address" name="address"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
    </div>
    
    <!-- Phone Field -->
    <div class="mb-3">
        <label for="phone" class="form-label">Phone (Optional)</label>
        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
    </div>
    
    <!-- Submit Button -->
    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">Register</button>
    </div>
    
    <!-- Login Link -->
    <div class="mt-3 text-center">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>