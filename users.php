<?php
// admin/users.php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Redirect if not admin
redirectIfNotAdmin();

$page_title = 'Manage Users';
require_once '../includes/header.php';

// Initialize User class
$user = new User($db);

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['delete'])) {
        $user->user_id = $_POST['user_id'];
        if($user->delete()) {
            $_SESSION['message'] = 'User deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete user';
        }
    }
    
    header('Location: users.php');
    exit();
}

// Get all users
$stmt = $user->read();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Display messages
if(isset($_SESSION['message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
}

if(isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Users</h1>
        <a href="user_add.php" class="btn btn-primary">Add New User</a>
    </div>
    
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Full Name</th>
                    <th>Admin</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                <tr>
                    <td><?php echo $user['user_id']; ?></td>
                    <td><?php echo $user['username']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['full_name']; ?></td>
                    <td><?php echo $user['is_admin'] ? 'Yes' : 'No'; ?></td>
                    <td>
                        <a href="user_edit.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="users.php" method="post" class="d-inline">
                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                            <input type="hidden" name="delete" value="1">
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>