<?php
require_once 'includes/config.php';
require_once 'includes/Book.php';
require_once 'includes/User.php';
require_once 'includes/functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$bookModel = new Book();
$book = $bookModel->getById($_GET['id']);

if (!$book) {
    header("Location: products.php");
    exit();
}

// Get related books (same category)
$relatedBooks = $bookModel->getAll(['category' => $book['category_id'], 'limit' => 4]);

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review']) && isLoggedIn()) {
    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['comment']);
    
    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        // Get database connection from config
        $db = (new Database())->getConnection();
        
        $stmt = $db->prepare(
            "INSERT INTO reviews (book_id, user_id, rating, comment) 
             VALUES (:book_id, :user_id, :rating, :comment)"
        );
        
        $stmt->execute([
            ':book_id' => $book['id'],
            ':user_id' => $_SESSION['user']['id'],
            ':rating' => $rating,
            ':comment' => $comment
        ]);
    }
}

// Get reviews for this book
$reviews = [];
$db = (new Database())->getConnection();
$stmt = $db->prepare(
    "SELECT r.*, u.name as user_name 
     FROM reviews r 
     JOIN users u ON r.user_id = u.id 
     WHERE r.book_id = :book_id 
     ORDER BY r.created_at DESC"
);
$stmt->execute([':book_id' => $book['id']]);
$reviews = $stmt->fetchAll();

// Calculate average rating
$avgRating = 0;
if (!empty($reviews)) {
    $sum = 0;
    foreach ($reviews as $review) {
        $sum += $review['rating'];
    }
    $avgRating = $sum / count($reviews);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
        <div class="row">
            <div class="col-md-5">
                <div class="card mb-4">
                    <img src="<?php echo UPLOAD_DIR . htmlspecialchars($book['image']); ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($book['title']); ?>">
                </div>
            </div>
            
            <div class="col-md-7">
                <h1><?php echo htmlspecialchars($book['title']); ?></h1>
                <p class="text-muted">by <?php echo htmlspecialchars($book['author']); ?></p>
                
                <?php if ($avgRating > 0): ?>
                    <div class="mb-3">
                        <?php 
                        $fullStars = floor($avgRating);
                        $halfStar = ($avgRating - $fullStars) >= 0.5;
                        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                        
                        for ($i = 0; $i < $fullStars; $i++) {
                            echo '<i class="bi bi-star-fill text-warning"></i> ';
                        }
                        if ($halfStar) {
                            echo '<i class="bi bi-star-half text-warning"></i> ';
                        }
                        for ($i = 0; $i < $emptyStars; $i++) {
                            echo '<i class="bi bi-star text-warning"></i> ';
                        }
                        ?>
                        <span class="ms-2">(<?php echo round($avgRating, 1); ?> from <?php echo count($reviews); ?> reviews)</span>
                    </div>
                <?php endif; ?>
                
                <div class="mb-4">
                    <h3><?php echo formatPrice($book['price']); ?></h3>
                    <p class="<?php echo $book['stock'] > 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $book['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                    </p>
                </div>
                
                <div class="mb-4">
                    <h4>Description</h4>
                    <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                </div>
                
                <div class="mb-4">
                    <h4>Details</h4>
                    <ul class="list-unstyled">
                        <li><strong>Category:</strong> <?php echo htmlspecialchars($book['category_name']); ?></li>
                        <li><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></li>
                        <li><strong>Pages:</strong> <?php echo htmlspecialchars($book['pages']); ?></li>
                        <li><strong>Publisher:</strong> <?php echo htmlspecialchars($book['publisher']); ?></li>
                        <li><strong>Published:</strong> <?php echo date('F Y', strtotime($book['published_date'])); ?></li>
                    </ul>
                </div>
                
                <?php if ($book['stock'] > 0): ?>
                    <form action="cart.php" method="post" class="mb-4">
                        <div class="row g-2">
                            <div class="col-auto">
                                <input type="number" name="quantity" value="1" min="1" max="<?php echo $book['stock']; ?>" class="form-control">
                            </div>
                            <div class="col-auto">
                                <input type="hidden" name="product_id" value="<?php echo $book['id']; ?>">
                                <input type="hidden" name="action" value="add">
                                <button type="submit" class="btn btn-primary">Add to Cart</button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Related Books -->
        <?php if (!empty($relatedBooks)): ?>
            <div class="mt-5">
                <h3>Related Books</h3>
                <div class="row">
                    <?php foreach ($relatedBooks as $relatedBook): ?>
                        <?php if ($relatedBook['id'] != $book['id']): ?>
                            <div class="col-md-3 mb-4">
                                <div class="card h-100">
                                    <a href="product.php?id=<?php echo $relatedBook['id']; ?>">
                                        <img src="<?php echo UPLOAD_DIR . htmlspecialchars($relatedBook['image']); ?>" 
                                             class="card-img-top" 
                                             alt="<?php echo htmlspecialchars($relatedBook['title']); ?>">
                                    </a>
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a href="product.php?id=<?php echo $relatedBook['id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($relatedBook['title']); ?>
                                            </a>
                                        </h5>
                                        <p class="card-text text-muted">by <?php echo htmlspecialchars($relatedBook['author']); ?></p>
                                        <p class="card-text"><?php echo formatPrice($relatedBook['price']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Reviews Section -->
        <div class="mt-5">
            <h3>Customer Reviews</h3>
            
            <?php if (isLoggedIn()): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5>Write a Review</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating</label>
                                <select class="form-select" id="rating" name="rating" required>
                                    <option value="">Select rating</option>
                                    <option value="5">5 - Excellent</option>
                                    <option value="4">4 - Very Good</option>
                                    <option value="3">3 - Average</option>
                                    <option value="2">2 - Poor</option>
                                    <option value="1">1 - Terrible</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Review</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                            </div>
                            <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    Please <a href="login.php">login</a> to write a review.
                </div>
            <?php endif; ?>
            
            <?php if (!empty($reviews)): ?>
                <div class="list-group">
                    <?php foreach ($reviews as $review): ?>
                        <div class="list-group-item mb-3">
                            <div class="d-flex justify-content-between">
                                <h5><?php echo htmlspecialchars($review['user_name']); ?></h5>
                                <div>
                                    <?php 
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $review['rating']) {
                                            echo '<i class="bi bi-star-fill text-warning"></i>';
                                        } else {
                                            echo '<i class="bi bi-star text-warning"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <small class="text-muted"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                            <p class="mt-2"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No reviews yet. Be the first to review!</div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>