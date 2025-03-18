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
    header("Location: display_sales.php");
    exit();
}

$sale_id = mysqli_real_escape_string($conn, $_GET['id']);
$sql = "SELECT * FROM sale_products WHERE id = '$sale_id'";
$result = mysqli_query($conn, $sql);

if (!$result) {
    debug_log("Error fetching sale: " . mysqli_error($conn));
    die("Database error");
}

$sale = mysqli_fetch_assoc($result);

if (!$sale) {
    header("Location: display_sales.php");
    exit();
}

// Define categories array
$categories = ['Ring', 'Necklace', 'Bangle', 'Bracelet', 'Earring'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug_log("POST data received: " . print_r($_POST, true));
    
    try {
        // Validate required fields
        $required_fields = ['saleName', 'salePrice', 'saleCategory'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("$field is required!");
            }
        }

        // Validate numeric fields
        if (!is_numeric($_POST['salePrice']) || $_POST['salePrice'] <= 0) {
            throw new Exception("Invalid price value!");
        }
        
        if (isset($_POST['saleDiscount']) && (!is_numeric($_POST['saleDiscount']) || $_POST['saleDiscount'] < 0)) {
            throw new Exception("Invalid discount value!");
        }

        // Prepare data with proper type casting and escaping
        $data = [
            'saleName' => mysqli_real_escape_string($conn, trim($_POST['saleName'])),
            'saleCategory' => mysqli_real_escape_string($conn, trim($_POST['saleCategory'])),
            'salePrice' => number_format(floatval($_POST['salePrice']), 2, '.', ''),
            'saleDiscount' => isset($_POST['saleDiscount']) ? number_format(floatval($_POST['saleDiscount']), 2, '.', '') : 0.00
        ];

        $updateFields = [];
        foreach ($data as $key => $value) {
            if (is_numeric($value)) {
                $updateFields[] = "$key = $value";
            } else {
                $updateFields[] = "$key = '$value'";
            }
        }

        // Handle image upload
        $upload_dir = "uploads/sales/";
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception("Failed to create upload directory");
            }
        }

        // Process sale image
        if (isset($_FILES['saleImage']) && $_FILES['saleImage']['size'] > 0) {
            $upload_result = handle_image_upload($_FILES['saleImage'], $upload_dir);
            if ($upload_result['success']) {
                // Delete old image if exists
                if (!empty($sale['saleImage']) && file_exists($sale['saleImage'])) {
                    unlink($sale['saleImage']);
                }
                $updateFields[] = "saleImage = '" . $upload_result['path'] . "'";
            }
        }

        // Construct and execute update query
        $updateQuery = "UPDATE sale_products SET " . implode(", ", $updateFields) . " WHERE id = '$sale_id'";
        debug_log("Update query: " . $updateQuery);

        if (!mysqli_query($conn, $updateQuery)) {
            throw new Exception("Database error: " . mysqli_error($conn));
        }

        $_SESSION['success_message'] = "Sale product updated successfully!";
        header("Location: display_sales.php");
        exit();

    } catch (Exception $e) {
        $error_message = $e->getMessage();
        debug_log("Error occurred: " . $error_message);
    }
}

// Helper function for image upload
function handle_image_upload($file, $upload_dir) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception("Invalid file type. Only JPG, PNG and GIF are allowed.");
    }

    if ($file['size'] > 5000000) { 
        throw new Exception("File is too large. Maximum size is 5MB.");
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid('sale_') . '.' . $file_extension;
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
    <title>Edit Sale Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Edit Sale Product</h1>
            <a href="display_sales.php" class="btn btn-secondary">Back to Sale Products</a>
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
                        <label for="saleName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="saleName" name="saleName" 
                               value="<?php echo htmlspecialchars($sale['saleName']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="saleCategory" class="form-label">Category</label>
                        <select class="form-select" id="saleCategory" name="saleCategory" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>"
                                        <?php echo ($sale['saleCategory'] === $category) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="salePrice" class="form-label">Price</label>
                        <input type="number" step="0.01" class="form-control" id="salePrice" name="salePrice" 
                               value="<?php echo htmlspecialchars($sale['salePrice']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="saleDiscount" class="form-label">Discount</label>
                        <input type="number" step="0.01" class="form-control" id="saleDiscount" name="saleDiscount" 
                               value="<?php echo htmlspecialchars($sale['saleDiscount']); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="saleImage" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="saleImage" name="saleImage" accept="image/*">
                        <?php if (!empty($sale['saleImage'])): ?>
                            <div class="mt-2">
                                <p>Current image:</p>
                                <img src="<?php echo htmlspecialchars($sale['saleImage']); ?>" 
                                     alt="Current sale image" style="max-width: 200px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Update Sale Product</button>
                        <a href="display_sales.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>