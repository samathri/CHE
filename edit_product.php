<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include './php/db.php';
session_start();

// Debug function
function debug_log($message) {
    error_log(print_r($message, true));
}

if (!isset($_GET['id'])) {
    header("Location: display_products.php");
    exit();
}

$product_id = mysqli_real_escape_string($conn, $_GET['id']);
$sql = "SELECT * FROM products WHERE id = '$product_id'";
$result = mysqli_query($conn, $sql);

if (!$result) {
    debug_log("Error fetching product: " . mysqli_error($conn));
    die("Database error");
}

$product = mysqli_fetch_assoc($result);

if (!$product) {
    header("Location: display_products.php");
    exit();
}

// Define categories array
$categories = ['Ring', 'Necklace', 'Bangle', 'Bracelet', 'Earring'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug_log("POST data received: " . print_r($_POST, true));
    
    try {
        // Validate required fields
        $required_fields = ['productName', 'productPrice', 'productCategory', 'quantity'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("$field is required!");
            }
        }

        // Validate numeric fields
        if (!is_numeric($_POST['productPrice']) || $_POST['productPrice'] <= 0) {
            throw new Exception("Invalid price value!");
        }
        
        if (!is_numeric($_POST['quantity']) || $_POST['quantity'] < 0) {
            throw new Exception("Invalid quantity value!");
        }

        // Prepare data with proper type casting and escaping
        $data = [
            'productName' => mysqli_real_escape_string($conn, trim($_POST['productName'])),
            'productCategory' => mysqli_real_escape_string($conn, trim($_POST['productCategory'])),
            'productPrice' => number_format(floatval($_POST['productPrice']), 2, '.', ''),
            'productDescription' => mysqli_real_escape_string($conn, trim($_POST['productDescription'])),
            'productSize' => mysqli_real_escape_string($conn, trim($_POST['productSize'])),
            'quantity' => intval($_POST['quantity'])
        ];

        $updateFields = [];
        foreach ($data as $key => $value) {
            if (is_numeric($value)) {
                $updateFields[] = "$key = $value";
            } else {
                $updateFields[] = "$key = '$value'";
            }
        }

        // Handle image uploads
        $upload_dir = "uploads/";
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception("Failed to create upload directory");
            }
        }

        // Process main image
        if (isset($_FILES['productImage']) && $_FILES['productImage']['size'] > 0) {
            $upload_result = handle_image_upload($_FILES['productImage'], $upload_dir);
            if ($upload_result['success']) {
                $updateFields[] = "productImage = '" . $upload_result['path'] . "'";
            }
        }

        // Process sub images
        for ($i = 1; $i <= 3; $i++) {
            if (isset($_FILES["subImage$i"]) && $_FILES["subImage$i"]['size'] > 0) {
                $upload_result = handle_image_upload($_FILES["subImage$i"], $upload_dir);
                if ($upload_result['success']) {
                    $updateFields[] = "subImage$i = '" . $upload_result['path'] . "'";
                }
            }
        }

        // Construct and execute update query
        $updateQuery = "UPDATE products SET " . implode(", ", $updateFields) . " WHERE id = '$product_id'";
        debug_log("Update query: " . $updateQuery);

        if (!mysqli_query($conn, $updateQuery)) {
            throw new Exception("Database error: " . mysqli_error($conn));
        }

        $_SESSION['success_message'] = "Product updated successfully!";
        header("Location: display_products.php");
        exit();

    } catch (Exception $e) {
        $error_message = $e->getMessage();
        debug_log("Error occurred: " . $error_message);
    }
}

// Helper function for image uploads
function handle_image_upload($file, $upload_dir) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception("Invalid file type. Only JPG, PNG and GIF are allowed.");
    }

    if ($file['size'] > 5000000) { 
        throw new Exception("File is too large. Maximum size is 5MB.");
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $upload_dir . $new_filename;

    if (!move_uploaded_file($file['tmp_name'], $target_file)) {
        throw new Exception("Failed to upload file.");
    }

    return ['success' => true, 'path' => $target_file];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Edit Product</h1>
            <a href="display_products.php" class="btn btn-secondary">Back to Products</a>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo htmlspecialchars($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="productName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="productName" name="productName" 
                               value="<?php echo htmlspecialchars($product['productName']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="productCategory" class="form-label">Category</label>
                        <select class="form-select" id="productCategory" name="productCategory" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>"
                                        <?php echo ($product['productCategory'] === $category) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="productPrice" class="form-label">Price</label>
                        <input type="number" step="0.01" class="form-control" id="productPrice" name="productPrice" 
                               value="<?php echo htmlspecialchars($product['productPrice']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="productDescription" name="productDescription" rows="3" 
                                  required><?php echo htmlspecialchars($product['productDescription']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="productSize" class="form-label">Size</label>
                        <input type="text" class="form-control" id="productSize" name="productSize" 
                               value="<?php echo htmlspecialchars($product['productSize']); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                               value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="productImage" class="form-label">Main Product Image</label>
                        <input type="file" class="form-control" id="productImage" name="productImage" accept="image/*">
                        <?php if (!empty($product['productImage'])): ?>
                            <div class="mt-2">
                                <p>Current image:</p>
                                <img src="<?php echo htmlspecialchars($product['productImage']); ?>" 
                                     alt="Current product image" style="max-width: 200px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <div class="mb-3">
                            <label for="subImage<?php echo $i; ?>" class="form-label">Additional Image <?php echo $i; ?></label>
                            <input type="file" class="form-control" id="subImage<?php echo $i; ?>" 
                                   name="subImage<?php echo $i; ?>" accept="image/*">
                            <?php if (!empty($product["subImage$i"])): ?>
                                <div class="mt-2">
                                    <p>Current image:</p>
                                    <img src="<?php echo htmlspecialchars($product["subImage$i"]); ?>" 
                                         alt="Current sub image <?php echo $i; ?>" style="max-width: 200px;">
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Update Product</button>
                        <a href="display_products.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>