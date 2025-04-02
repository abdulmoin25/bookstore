<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/Book.php';
require_once __DIR__ . '/../includes/User.php';

// Secure admin access
requireAdmin();

$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';

try {
    // Initialize models
    $bookModel = new Book();
    $userModel = new User();

    // Get counts with error handling
    $totalBooks = $bookModel->countAll();
    $totalUsers = $userModel->countAll();
    
    // Get recent orders (last 5)
    $db = (new Database())->getConnection();
    $stmt = $db->query("SELECT o.id, o.total_amount, o.created_at, u.name as user_name 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       ORDER BY o.created_at DESC LIMIT 5");
    $recentOrders = $stmt->fetchAll();
    
} catch (Exception $e) {
    // Log error and show user-friendly message
    error_log("Admin dashboard error: " . $e->getMessage());
    $_SESSION['error'] = "Could not load dashboard data. Please try again.";
    header("Location: error.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-dashboard">
    <?php include __DIR__ . '/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Dashboard Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="card text-white bg-primary h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Total Books</h5>
                                        <p class="card-text display-4 mb-0"><?php echo number_format($totalBooks); ?></p>
                                    </div>
                                    <i class="bi bi-book display-4 opacity-50"></i>
                                </div>
                                <a href="books.php" class="stretched-link text-white text-decoration-none"></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-white bg-success h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Total Users</h5>
                                        <p class="card-text display-4 mb-0"><?php echo number_format($totalUsers); ?></p>
                                    </div>
                                    <i class="bi bi-people display-4 opacity-50"></i>
                                </div>
                                <a href="users.php" class="stretched-link text-white text-decoration-none"></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h2 class="h5 mb-0">Recent Orders</h2>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="15%">Order ID</th>
                                        <th width="30%">Customer</th>
                                        <th width="25%">Date</th>
                                        <th width="20%">Amount</th>
                                        <th width="10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recentOrders)): ?>
                                        <?php foreach ($recentOrders as $order): ?>
                                            <tr>
                                                <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                                <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <a href="orders.php?action=view&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No recent orders</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>