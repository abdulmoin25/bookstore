<?php
// admin/user_add.php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Redirect if not admin
redirectIfNotAdmin();

$page_title = 'Add User';
require_once '../includes/header.php';

// Initialize User class
$user = new User($db);

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user->username = $_POST['username'];
    $user->password = $_POST['password'];
    $user->email = $_POST['email'];
    $user->full_name = $_POST['full_name'];
    $user->address = $_POST['address'];
    $user->phone = $_POST['phone'];
    $user->is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    // Validate inputs
    $errors = [];
    
    if(empty($user->username)) {
        $errors[] = 'Username is required';
    }
    
    if(empty($user->password)) {
        $errors[] = 'Password is required';
    } elseif(strlen($user->password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if(empty($user->email)) {
        $errors[] = 'Email is required';
    } elseif(!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if(empty($user->full_name)) {
        $errors[] = 'Full name is required';
    }
    
    if(empty($user->address)) {
        $errors[] = 'Address is required';
    }
    
    if(empty($user->phone)) {
        $errors[] = 'Phone number is required';
    }
    
    // Check if username or email already exists
    $user->username = $_POST['username'];
    if($user->usernameExists()) {
        $errors[] = 'Username already taken';
    }
    
    $user->email = $_POST['email'];
    if($user->emailExists()) {
        $errors[] = 'Email already registered';
    }
    
    if(empty($errors)) {
        if($user->register()) {
            // Update admin status (register() doesn't handle this)
            $user->user_id = $db->lastInsertId();
            $user->is_admin = $_POST['is_admin'] ? 1 : 0;
            $user->update();
            
            $_SESSION['message'] = 'User added successfully';
            header('Location: users.php');
            exit();
        } else {
            $errors[] = 'Failed to add user';
        }
    }
}
?>

<div class="container">
    <h1 class="mb-4">Add New User</h1>
    
    <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach($errors as $error): ?>
                <p class="mb-0"><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form action="user_add.php" method="post">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="phone" name="phone" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin">
            <label class="form-check-label" for="is_admin">Admin User</label>
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Add User</button>
            <a href="users.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>