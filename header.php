<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'functions.php';
require_once 'Cart.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cart = new Cart();
$cartCount = $cart->getCount();
$cartTotal = $cart->getTotal();

// Get current page for active nav highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Initialize Book model for categories
$bookModel = new Book();
$categories = $bookModel->getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <!-- Favicon -->
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
    <!-- Top Announcement Bar -->
    <div class="announcement-bar bg-primary text-white py-2">
        <div class="container text-center">
            <p class="mb-0">Free shipping on orders over $50 | <a href="promotions.php" class="text-white">Shop Now</a></p>
        </div>
    </div>

    <!-- Main Header -->
    <header class="bg-white shadow-sm sticky-top">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center py-2">
                <!-- Logo/Brand -->
                <a class="navbar-brand" href="index.php">
                    <img src="assets/images/logo.png" alt="<?php echo htmlspecialchars(SITE_NAME); ?>" height="40">
                </a>
                
                <!-- Search Form (Visible on desktop) -->
                <form class="d-none d-lg-flex w-50 mx-4" action="products.php" method="get">
                    <div class="input-group">
                        <input class="form-control" type="search" name="search" placeholder="Search books..." aria-label="Search">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- User/Cart Links -->
                <div class="d-flex align-items-center">
                    <a href="cart.php" class="btn btn-outline-dark position-relative me-3">
                        <i class="bi bi-cart"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo htmlspecialchars($cartCount); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    
                    <?php if (isLoggedIn()): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-dark dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="orders.php"><i class="bi bi-list-check me-2"></i>My Orders</a></li>
                                <?php if (isAdmin()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="admin/"><i class="bi bi-speedometer2 me-2"></i>Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="btn-group">
                            <a href="login.php" class="btn btn-outline-dark">Login</a>
                            <a href="register.php" class="btn btn-dark">Register</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Main Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light py-0">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?php echo in_array($currentPage, ['products.php', 'product.php']) ? 'active' : ''; ?>" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Categories
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="categoriesDropdown">
                                <?php foreach ($categories as $category): ?>
                                    <li>
                                        <a class="dropdown-item" href="products.php?category=<?php echo htmlspecialchars($category['id']); ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'products.php' ? 'active' : ''; ?>" href="products.php">All Books</a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'about.php' ? 'active' : ''; ?>" href="about.php">About</a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'contact.php' ? 'active' : ''; ?>" href="contact.php">Contact</a>
                        </li>
                    </ul>
                </div>
                
                <!-- Search Form (Visible on mobile) -->
                <form class="d-lg-none w-100 my-2" action="products.php" method="get">
                    <div class="input-group">
                        <input class="form-control" type="search" name="search" placeholder="Search books..." aria-label="Search">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </nav>
        </div>
    </header>

    <main class="container my-4">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars($_SESSION['message_type']); ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>
    </main>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Your custom JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>