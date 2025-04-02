<?php
require_once 'includes/config.php';
require_once 'includes/Book.php';
require_once 'includes/functions.php';

/**
 * Format price with currency symbol and decimal places
 * 
 * @param float $price The price to format
 * @param string $currency The currency symbol (default: '$')
 * @param int $decimals Number of decimal places (default: 2)
 * @return string Formatted price string
 */
function formatPrice($price, $currency = '$', $decimals = 2) {
    return $currency . number_format($price, $decimals);
}

$bookModel = new Book();
$categories = $bookModel->getCategories();

// Get filters from query string
$filters = [];
if (!empty($_GET['category'])) {
    $filters['category'] = (int)$_GET['category'];
}
if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}
if (!empty($_GET['min_price'])) {
    $filters['min_price'] = (float)$_GET['min_price'];
}
if (!empty($_GET['max_price'])) {
    $filters['max_price'] = (float)$_GET['max_price'];
}

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Get total count for pagination
$totalBooks = count($bookModel->getAll($filters));
$totalPages = ceil($totalBooks / $perPage);

// Add pagination to filters
$filters['limit'] = $perPage;
$filters['offset'] = $offset;

// Get filtered books
$books = $bookModel->getAll($filters);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <!-- Filters sidebar -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Filters</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="products.php">
                            <div class="mb-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"
                                            <?php echo (!empty($filters['category']) && $filters['category'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Price Range</label>
                                <div class="row g-2">
                                    <div class="col">
                                        <input type="number" class="form-control" placeholder="Min" 
                                               name="min_price" min="0" step="0.01"
                                               value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>">
                                    </div>
                                    <div class="col">
                                        <input type="number" class="form-control" placeholder="Max" 
                                               name="max_price" min="0" step="0.01"
                                               value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                            <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">Reset</a>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Our Books</h2>
                    <div>
                        <?php if (isLoggedIn() && isAdmin()): ?>
                            <a href="admin/books.php?action=add" class="btn btn-success">Add New Book</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (empty($books)): ?>
                    <div class="alert alert-info">No books found matching your criteria.</div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($books as $book): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <img src="<?php echo UPLOAD_DIR . htmlspecialchars($book['image']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($book['title']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                        <p class="card-text text-muted">by <?php echo htmlspecialchars($book['author']); ?></p>
                                        <p class="card-text"><?php echo formatPrice($book['price']); ?></p>
                                        <p class="card-text">
                                            <small class="text-<?php echo $book['stock'] > 0 ? 'success' : 'danger'; ?>">
                                                <?php echo $book['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                            </small>
                                        </p>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="product.php?id=<?php echo $book['id']; ?>" 
                                           class="btn btn-primary btn-sm">Details</a>
                                        <?php if ($book['stock'] > 0): ?>
                                            <button class="btn btn-outline-secondary btn-sm add-to-cart" 
                                                    data-id="<?php echo $book['id']; ?>">
                                                Add to Cart
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" 
                                           href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/cart.js"></script>
</body>
</html>