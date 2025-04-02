<?php
// checkout.php
$page_title = 'Checkout';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/config.php';
require_once 'includes/Cart.php';
require_once 'includes/User.php';
require_once 'includes/functions.php';

// Initialize database connection and cart
$database = new Database();
$db = $database->getConnection();
$cart = new Cart($db);

// Redirect if cart is empty
if(count($cart->getItems()) == 0) {
    $_SESSION['message'] = 'Your cart is empty';
    $_SESSION['message_type'] = 'warning';
    header('Location: cart.php');
    exit();
}

// Handle checkout form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process order
    if(isLoggedIn()) {
        // Create order
        $user_id = $_SESSION['user']['id']; // Changed from user_id to user['id']
        $cart_contents = $cart->getItems();
        $total_amount = $cart->getTotal();
        
        try {
            $db->beginTransaction();
            
            // Insert order
            $query = 'INSERT INTO orders (user_id, total_amount, status) VALUES (:user_id, :total_amount, "pending")';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':total_amount', $total_amount);
            $stmt->execute();
            $order_id = $db->lastInsertId();
            
            // Insert order items
            $query = 'INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)';
            $stmt = $db->prepare($query);
            
            foreach($cart_contents as $item) {
                $stmt->bindParam(':order_id', $order_id);
                $stmt->bindParam(':product_id', $item['id']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':price', $item['price']);
                $stmt->execute();
                
                // Update product stock
                $query = 'UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :product_id';
                $update_stmt = $db->prepare($query);
                $update_stmt->bindParam(':quantity', $item['quantity']);
                $update_stmt->bindParam(':product_id', $item['id']);
                $update_stmt->execute();
            }
            
            $db->commit();
            
            // Get user details for invoice
            $user = new User($db);
            $user->id = $user_id;
            $user_details = $user->getUserById(); // Changed from read_single() to getUserById()
            
            // Clear cart
            $cart->clear();
            
            // Set success message
            $_SESSION['message'] = "Order #$order_id placed successfully!";
            $_SESSION['message_type'] = 'success';
            
            // Redirect to confirmation page
            header('Location: order_confirmation.php?order_id='.$order_id);
            exit();
            
        } catch(PDOException $e) {
            if($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['error'] = 'Error processing your order: ' . $e->getMessage();
            header('Location: checkout.php');
            exit();
        }
    } else {
        // User needs to login or register
        $_SESSION['checkout_redirect'] = true;
        $_SESSION['message'] = 'Please login or register to complete your order';
        $_SESSION['message_type'] = 'warning';
        header('Location: login.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php 
    // Include header
    require_once 'includes/header.php'; 
    
    // Display messages
    if(isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    ?>

    <main class="container my-4">
        <h1 class="mb-4">Checkout</h1>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h3 class="mb-0">Order Summary</h3>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($cart->getItems() as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                                    <td class="text-end">$<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="text-end"><?php echo $item['quantity']; ?></td>
                                    <td class="text-end">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th class="text-end">$<?php echo number_format($cart->getTotal(), 2); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h3 class="mb-0">Complete Order</h3>
                    </div>
                    <div class="card-body">
                        <?php if(isLoggedIn()): ?>
                            <?php 
                            $user = new User($db);
                            $user->id = $_SESSION['user']['id'];
                            $user_details = $user->getUserById();
                            ?>
                            <div class="mb-3">
                                <h5>Shipping To:</h5>
                                <p>
                                    <?php echo htmlspecialchars($user_details['full_name'] ?? 'Not provided'); ?><br>
                                    <?php echo htmlspecialchars($user_details['address'] ?? 'Not provided'); ?><br>
                                    Phone: <?php echo htmlspecialchars($user_details['phone'] ?? 'Not provided'); ?>
                                </p>
                            </div>
                            <form action="checkout.php" method="post">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">Place Order</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <p>You need to <a href="login.php">login</a> or <a href="register.php">register</a> to complete your order.</p>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="login.php" class="btn btn-primary">Login</a>
                                <a href="register.php" class="btn btn-outline-primary">Register</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once 'includes/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>