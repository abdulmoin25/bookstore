<?php
// admin/product_edit.php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Redirect if not admin
redirectIfNotAdmin();

$page_title = 'Edit Product';
require_once '../includes/header.php';

// Initialize Product class
$product = new Product($db);

// Get product ID from URL
if(!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product->product_id = $_GET['id'];

// Fetch product data
if(!$product->read_single()) {
    header('Location: products.php');
    exit();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product->category_id = $_POST['category_id'];
    $product->product_name = $_POST['product_name'];
    $product->description = $_POST['description'];
    $product->price = $_POST['price'];
    $product->stock_quantity = $_POST['stock_quantity'];
    
    // Handle file upload if new image is provided
    if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/';
        $file_name = basename($_FILES['image']['name']);
        $file_path = $upload_dir . $file_name;
        $file_type = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if($check === false) {
            $errors[] = 'File is not an image.';
        }
        
        // Check file size (max 2MB)
        if($_FILES['image']['size'] > 2000000) {
            $errors[] = 'File is too large (max 2MB).';
        }
        
        // Allow certain file formats
        if($file_type != 'jpg' && $file_type != 'png' && $file_type != 'jpeg' && $file_type != 'gif') {
            $errors[] = 'Only JPG, JPEG, PNG & GIF files are allowed.';
        }
        
        if(empty($errors)) {
            if(move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                // Delete old image if it exists
                if($product->image_path && file_exists('../assets/images/' . $product->image_path)) {
                    unlink('../assets/images/' . $product->image_path);
                }
                $product->image_path = $file_name;
            } else {
                $errors[] = 'Error uploading file.';
            }
        }
    }
    
    if(empty($errors)) {
        if($product->update()) {
            $_SESSION['message'] = 'Product updated successfully';
            header('Location: products.php');
            exit();
        } else {
            $errors[] = 'Failed to update product';
        }
    }
}

// Get categories for dropdown
$categories = $db->query('SELECT * FROM categories')->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1 class="mb-4">Edit Product</h1>
    
    <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach($errors as $error): ?>
                <p class="mb-0"><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form action="product_edit.php?id=<?php echo $product->product_id; ?>" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="product_name" class="form-label">Product Name</label>
            <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo $product->product_name; ?>" required>
        </div>
        <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select class="form-select" id="category_id" name="category_id" required>
                <option value="">Select Category</option>
                <?php foreach($categories as $category): ?>
                    <option value="<?php echo $category['category_id']; ?>" <?php echo ($category['category_id'] == $product->category_id) ? 'selected' : ''; ?>>
                        <?php echo $category['category_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo $product->description; ?></textarea>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo $product->price; ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="stock_quantity" class="form-label">Stock Quantity</label>
                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="<?php echo $product->stock_quantity; ?>" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Product Image</label>
            <?php if($product->image_path): ?>
                <div class="mb-2">
                    <img src="../assets/images/<?php echo $product->image_path; ?>" alt="<?php echo $product->product_name; ?>" style="max-width: 200px;">
                    <p class="text-muted mt-1">Current image: <?php echo $product->image_path; ?></p>
                </div>
            <?php endif; ?>
            <input type="file" class="form-control" id="image" name="image">
            <small class="text-muted">Leave blank to keep current image</small>
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="products.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>