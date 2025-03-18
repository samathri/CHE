<?php
include './php/db.php';
session_start();

// Function to handle image upload for products
function handleImageUpload($file, $uploadDir) {
    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $file['tmp_name']);
        
        if (in_array($mimeType, $allowedTypes)) {
            $file_name = $file['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $new_file_name = uniqid() . '.' . $file_ext;
            
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $new_file_name)) {
                finfo_close($fileInfo);
                return $uploadDir . $new_file_name;
            }
        }
        finfo_close($fileInfo);
    }
    return '';
}

// Function to handle sales image upload
function handleSalesImageUpload($file, $uploadDir) {
    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $file['tmp_name']);
        
        if (in_array($mimeType, $allowedTypes)) {
            $file_name = $file['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $new_file_name = uniqid('sale_') . '.' . $file_ext;
            
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $new_file_name)) {
                finfo_close($fileInfo);
                return $uploadDir . $new_file_name;
            }
        }
        finfo_close($fileInfo);
    }
    return '';
}

// Process product form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product-name'])) {
    if (!isset($_SESSION['form_token'])) {
        $_SESSION['form_token'] = md5(uniqid());
    }
    
    // Check if this is a duplicate submission
    if (!isset($_POST['form_token']) || $_POST['form_token'] !== $_SESSION['form_token']) {
        $productName = isset($_POST['product-name']) ? 
            mysqli_real_escape_string($conn, trim($_POST['product-name'])) : '';
        $productCategory = isset($_POST['product-category']) ? 
            mysqli_real_escape_string($conn, trim($_POST['product-category'])) : '';
        $productDescription = isset($_POST['product-description']) ? 
            mysqli_real_escape_string($conn, trim($_POST['product-description'])) : '';
        
        // Handle numeric fields with proper validation
        $productPrice = 0.00;
        $productDiscount = 0.00;
        $productSize = 0.00;
        $quantity = 0;

        // Price validation
        if (isset($_POST['product-price'])) {
            $cleanPrice = preg_replace('/[^0-9.]/', '', $_POST['product-price']);
            if (is_numeric($cleanPrice)) {
                $productPrice = number_format((float)$cleanPrice, 2, '.', '');
            }
        }

        // Discount validation
        if (isset($_POST['product-discount'])) {
            $cleanDiscount = preg_replace('/[^0-9.]/', '', $_POST['product-discount']);
            if (is_numeric($cleanDiscount)) {
                $productDiscount = number_format((float)$cleanDiscount, 2, '.', '');
            }
        }

        // Size validation
        if (isset($_POST['product-size'])) {
            $cleanSize = preg_replace('/[^0-9.]/', '', $_POST['product-size']);
            if (is_numeric($cleanSize)) {
                $productSize = number_format((float)$cleanSize, 2, '.', '');
            }
        }

        // Quantity validation
        if (isset($_POST['product-quantity'])) {
            $cleanQuantity = preg_replace('/[^0-9]/', '', $_POST['product-quantity']);
            if (is_numeric($cleanQuantity)) {
                $quantity = (int)$cleanQuantity;
            }
        }

        // Validate required fields
        $errors = [];
        if (empty($productName)) $errors[] = "Product name is required";
        if (empty($productCategory)) $errors[] = "Product category is required";
        if (empty($productDescription)) $errors[] = "Product description is required";
        if ($productPrice <= 0) $errors[] = "Valid product price is required";
        if ($quantity <= 0) $errors[] = "Valid product quantity is required";

        // Process images
        $uploadDir = 'uploads/products/';
        $productImage = handleImageUpload($_FILES['product-images'], $uploadDir);
        $subImage1 = handleImageUpload($_FILES['sub-image-1'], $uploadDir);
        $subImage2 = handleImageUpload($_FILES['sub-image-2'], $uploadDir);
        $subImage3 = handleImageUpload($_FILES['sub-image-3'], $uploadDir);

        if (empty($productImage)) {
            $errors[] = "Main product image is required";
        }

        // Process form if no errors
        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO products (productName, productCategory, productPrice, productDescription, productDiscount, productSize, quantity, productImage, subImage1, subImage2, subImage3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdsdsissss", 
                $productName, 
                $productCategory, 
                $productPrice, 
                $productDescription, 
                $productDiscount,
                $productSize,
                $quantity,
                $productImage, 
                $subImage1, 
                $subImage2, 
                $subImage3
            );
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Product added successfully!';
                unset($_SESSION['form_token']);
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $_SESSION['error_message'] = 'Error adding product: ' . $stmt->error;
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = 'Errors found: ' . implode("\n", $errors);
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Process sales form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sale-name'])) {
    // Validate and sanitize input
    $saleName = isset($_POST['sale-name']) ? 
        mysqli_real_escape_string($conn, trim($_POST['sale-name'])) : '';
    $saleCategory = isset($_POST['sale-category']) ? 
        mysqli_real_escape_string($conn, trim($_POST['sale-category'])) : '';
    
    // Handle numeric fields with proper validation
    $salePrice = 0.00;
    $saleDiscount = 0.00;

    // Price validation
    if (isset($_POST['sale-price'])) {
        $cleanPrice = preg_replace('/[^0-9.]/', '', $_POST['sale-price']);
        if (is_numeric($cleanPrice)) {
            $salePrice = number_format((float)$cleanPrice, 2, '.', '');
        }
    }

    // Discount validation
    if (isset($_POST['sale-discount'])) {
        $cleanDiscount = preg_replace('/[^0-9.]/', '', $_POST['sale-discount']);
        if (is_numeric($cleanDiscount)) {
            $saleDiscount = number_format((float)$cleanDiscount, 2, '.', '');
        }
    }

    // Validate required fields
    $errors = [];
    if (empty($saleName)) $errors[] = "Sale product name is required";
    if (empty($saleCategory)) $errors[] = "Sale category is required";
    if ($salePrice <= 0) $errors[] = "Valid sale price is required";

    // Process sale image
    $uploadDir = 'uploads/sales/';
    $saleImage = handleSalesImageUpload($_FILES['sale-images'], $uploadDir);

    if (empty($saleImage)) {
        $errors[] = "Sale product image is required";
    }

    // Process form if no errors
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO sale_products (saleName, saleCategory, salePrice, saleDiscount, saleImage) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdds", 
            $saleName, 
            $saleCategory, 
            $salePrice, 
            $saleDiscount,
            $saleImage
        );
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Sale product added successfully!';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['error_message'] = 'Error adding sale product: ' . $stmt->error;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = 'Errors found in sale form: ' . implode("\n", $errors);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Display messages if they exist
if (isset($_SESSION['success_message'])) {
    echo "<script>alert('" . htmlspecialchars($_SESSION['success_message']) . "');</script>";
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo "<script>alert('" . htmlspecialchars($_SESSION['error_message']) . "');</script>";
    unset($_SESSION['error_message']);
}

// Define categories array
$categories = ['Ring', 'Necklace', 'Bangle', 'Bracelet', 'Earring'];

// Handle delete request for products
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['delete_id']);
    
    $sql = "SELECT productImage, subImage1, subImage2, subImage3 FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    
    $delete_sql = "DELETE FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        if (!empty($product['productImage'])) unlink($product['productImage']);
        if (!empty($product['subImage1'])) unlink($product['subImage1']);
        if (!empty($product['subImage2'])) unlink($product['subImage2']);
        if (!empty($product['subImage3'])) unlink($product['subImage3']);
        
        echo json_encode(['success' => true]);
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete product']);
        exit;
    }
}

// Handle delete request for sales
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_sale_id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['delete_sale_id']);
    
    $sql = "SELECT saleImage FROM sale_products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $sale = mysqli_fetch_assoc($result);
    
    $delete_sql = "DELETE FROM sale_products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        if (!empty($sale['saleImage'])) unlink($sale['saleImage']);
        
        echo json_encode(['success' => true]);
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete sale']);
        exit;
    }
}

// Handle delete request for orders
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order_id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['delete_order_id']);
    
    $delete_sql = "DELETE FROM orders WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete order']);
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    
</head>
<body>

<div class="dashboard-header">
    <h1>Dashboard</h1>
    <div>
        <span>John Smith</span>
        <img src="images/image 1.jpeg" alt="Profile Picture">
    </div>
</div>

<div class="container">
    
<div class="card">
    <div class="card-header">Product Details</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Description</th>
                    <th>Size</th>
                    <th>Quantity</th>
                    <th>Additional Images</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch only 3 products from database
                $sql = "SELECT * FROM products LIMIT 3";
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td><img src='" . htmlspecialchars($row['productImage']) . "' alt='Product Image' style='width: 50px; height: 50px; object-fit: cover;'></td>";
                        echo "<td>" . htmlspecialchars($row['productName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['productCategory']) . "</td>";
                        echo "<td>RS." . number_format($row['productPrice'], 2) . "</td>";
                        echo "<td>RS." . number_format($row['productDiscount'], 2) . "</td>";
                        echo "<td>" . substr(htmlspecialchars($row['productDescription']), 0, 50) . "...</td>";
                        echo "<td>" . number_format($row['productSize'], 2) . "</td>";
                        echo "<td>" . htmlspecialchars($row['quantity'] ?? '0') . "</td>";
                        echo "<td>";
                        // Display additional images as thumbnails
                        if (!empty($row['subImage1'])) {
                            echo "<img src='" . htmlspecialchars($row['subImage1']) . "' alt='Sub Image 1' style='width: 30px; height: 30px; margin-right: 5px; object-fit: cover;'>";
                        }
                        if (!empty($row['subImage2'])) {
                            echo "<img src='" . htmlspecialchars($row['subImage2']) . "' alt='Sub Image 2' style='width: 30px; height: 30px; margin-right: 5px; object-fit: cover;'>";
                        }
                        if (!empty($row['subImage3'])) {
                            echo "<img src='" . htmlspecialchars($row['subImage3']) . "' alt='Sub Image 3' style='width: 30px; height: 30px; object-fit: cover;'>";
                        }
                        echo "</td>";
                        echo "<td>
                                <a href='edit_product.php?id=" . $row['id'] . "' class='btn btn-success btn-sm'>Edit</a>
                                <button class='btn btn-danger btn-sm delete-btn' data-id='" . $row['id'] . "'>Delete</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10' class='text-center'>No products found</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="text-end mt-3">
            <a href="display_products.php" class="btn btn-primary">See More</a>
        </div>
    </div>
</div>


    <div class="card">
        <div class="card-header">Sales Details</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Fetch only 3 products from database
                $sql = "SELECT * FROM sale_products LIMIT 3";
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td><img src='" . htmlspecialchars($row['saleImage']) . "' alt='Product Image' style='width: 50px; height: 50px; object-fit: cover;'></td>";
                        echo "<td>" . htmlspecialchars($row['saleName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['saleCategory']) . "</td>";
                        echo "<td>RS." . number_format($row['salePrice'], 2) . "</td>";
                        echo "<td>RS." . number_format($row['saleDiscount'], 2) . "</td>";
                        echo "</td>";
                        echo "<td>
                                <a href='edit_sales.php?id=" . $row['id'] . "' class='btn btn-success btn-sm'>Edit</a>
                                <button class='btn btn-danger btn-sm delete-btn' data-id='" . $row['id'] . "'>Delete</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10' class='text-center'>No products found</td></tr>";
                }
                ?>
            </tbody>
            </table>
            <div class="text-end mt-3">
            <a href="display_sales.php" class="btn btn-primary">See More</a>
        </div>
        </div>
    </div>


 
    <div class="card">
    <div class="card-header">User Details</div>
    <div class="card-body">
        
        <table class="table table-striped">
            <thead>
                <tr>
                   <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch only 3 users from database
                $sql = "SELECT user_registration.id,user_registration.user_email,user_address.firstName,user_address.lastName,user_address.phone FROM user_registration 
                INNER JOIN user_address
                ON user_registration.id = user_address.user_id";
                
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['firstName']) . " " . htmlspecialchars($row['lastName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['user_email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                       
                        
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No users found</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="text-end mt-3">
            <a href="display_users.php" class="btn btn-primary">See More</a>
        </div>
    </div>
</div>
  
<div class="card">
    <div class="card-header">Customer Feedback</div>
    <div class="card-body">
        <div class="feedback d-flex justify-content-between">
            <?php
            $sql_reviews = "SELECT r.rating, u.full_name, r.review 
                           FROM review r 
                           JOIN user_registration u ON r.user_id = u.id 
                           ORDER BY r.id DESC 
                           LIMIT 5";
            $result_reviews = $conn->query($sql_reviews);

            if ($result_reviews && $result_reviews->num_rows > 0) {
                while ($row = $result_reviews->fetch_assoc()) {
                    echo '<div style="width: 19%;">'; 
                    echo '<p><strong>' . htmlspecialchars($row['full_name']) . '</strong></p>';
                    echo '<p>' . htmlspecialchars($row['review']) . '</p>';
                    echo '<p>' . str_repeat('⭐', (int)$row['rating']) . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<p class="text-center w-100">No reviews available.</p>';
            }
            ?>
        </div>
        <div class="text-end mt-3">
            <a href="display_reviews.php" class="btn btn-primary">See More</a>
        </div>
    </div>
</div>
    
<div class="card">
        <div class="card-header">Order Details</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User Name</th>
                        <th>product_name</th>
                        <th>size</th>
                        <th>quantity</th>
                        <th>price</th>
                        <th>total</th>
                        <th>Order Date</th>
                        
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Fetch data from the orders table and join with the product table
                $sql = "
                    SELECT 
                        orders.id ,
                        orders.user_id,
                        orders.size,
                        orders.quantity,
                        orders.price,
                        orders.total,
                        orders.order_date,
                        products.productName 
                        
                    FROM orders
                    INNER JOIN products ON orders.product_id = products.id
                    LIMIT 3
                ";
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['productName']) . "</td>";
                        echo "<td>".  htmlspecialchars($row['size'])."</td>";
                        echo "<td>".  htmlspecialchars($row['quantity'])."</td>";
                        echo "<td>".  htmlspecialchars($row['price'])."</td>";
                        echo "<td>".  htmlspecialchars($row['total'])."</td>";
                        echo "<td>".  htmlspecialchars($row['order_date'])."</td>";
                       
                        echo "<td>
                                <button class='btn btn-danger btn-sm delete-order-btn' data-id='" . $row['id'] . "'>Delete</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No orders found</td></tr>";
                }
                ?>
                   
                </tbody>
            </table>
            <div class="text-end mt-3">
            <a href="order.php" class="btn btn-primary">See More</a>
        </div>
        </div>
    </div>


    <div class="card product-upload">
    <div class="card-header">Product Upload</div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="form_token" value="<?php echo $_SESSION['form_token'] ?? ''; ?>">
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="product-name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="product-name" name="product-name" placeholder="Enter product name" required>
                    </div>
                    <div class="mb-3">
                        <label for="product-category" class="form-label">Category</label>
                        <select class="form-select" id="product-category" name="product-category" required>
                            <option value="">Select a category</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>">
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="product-price" class="form-label">Product Price</label>
                        <input type="text" class="form-control" id="product-price" name="product-price" placeholder="Enter Price" required>
                    </div>
                    <div class="mb-3">
                        <label for="product-description" class="form-label">Product Description</label>
                        <textarea class="form-control" id="product-description" name="product-description" rows="4" placeholder="Enter detailed description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="product-discount" class="form-label">Product Discount</label>
                        <input type="text" class="form-control" id="product-discount" name="product-discount" placeholder="Enter Discount" required>
                    </div>
                    <div class="mb-3">
                        <label for="product-size" class="form-label">Product Size</label>
                        <input type="text" class="form-control" id="product-size" name="product-size" placeholder="Enter Size" required>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="product-quantity" class="form-label">Product Quantity</label>
                        <input type="text" class="form-control" id="product-quantity" name="product-quantity" placeholder="Enter Quantity" required>
                    </div>
                    <div class="mb-3">
                        <label for="product-images" class="form-label">Main Product Image</label>
                        <input type="file" class="form-control" id="product-images" name="product-images" required>
                        <div id="main-image-preview" class="mt-3 d-flex flex-wrap"></div>
                    </div>
                    <div class="mb-3">
                        <label for="sub-image-1" class="form-label">Additional Image 1</label>
                        <input type="file" class="form-control" id="sub-image-1" name="sub-image-1">
                        <div id="sub-image-1-preview" class="mt-3 d-flex flex-wrap"></div>
                    </div>
                    <div class="mb-3">
                        <label for="sub-image-2" class="form-label">Additional Image 2</label>
                        <input type="file" class="form-control" id="sub-image-2" name="sub-image-2">
                        <div id="sub-image-2-preview" class="mt-3 d-flex flex-wrap"></div>
                    </div>
                    <div class="mb-3">
                        <label for="sub-image-3" class="form-label">Additional Image 3</label>
                        <input type="file" class="form-control" id="sub-image-3" name="sub-image-3">
                        <div id="sub-image-3-preview" class="mt-3 d-flex flex-wrap"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!--sales-->

<div class="card product-upload">
    <div class="card-header">Sales Upload</div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="form_token" value="<?php echo $_SESSION['form_token'] ?? ''; ?>">
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="sale-name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="sale-name" name="sale-name" placeholder="Enter product name" required>
                    </div>
                    <div class="mb-3">
                        <label for="sale-category" class="form-label">Category</label>
                        <select class="form-select" id="sale-category" name="sale-category" required>
                            <option value="">Select a category</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>">
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="sale-price" class="form-label">Sale Price</label>
                        <input type="text" class="form-control" id="sale-price" name="sale-price" placeholder="Enter Sale Price" required>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="sale-discount" class="form-label">Discount</label>
                        <input type="text" class="form-control" id="sale-discount" name="sale-discount" placeholder="Enter Sale Discount" required>
                    </div>
                    <div class="mb-3">
                        <label for="sale-images" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="sale-images" name="sale-images" required>
                        <div id="main-image-preview" class="mt-3 d-flex flex-wrap"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function setupImagePreview(inputId, previewId) {
            const imageInput = document.getElementById(inputId);
            const imagePreview = document.getElementById(previewId);
            
            imageInput.addEventListener('change', function(e) {
                imagePreview.innerHTML = '';
                const file = e.target.files[0];
                
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.createElement('div');
                        preview.className = 'preview-item me-2 mb-2';
                        preview.innerHTML = `
                            <img src="${e.target.result}" alt="Preview" style="max-width: 100px; max-height: 100px;">
                        `;
                        imagePreview.appendChild(preview);
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        // Setup preview for all image inputs
        setupImagePreview('product-images', 'main-image-preview');
        setupImagePreview('sub-image-1', 'sub-image-1-preview');
        setupImagePreview('sub-image-2', 'sub-image-2-preview');
        setupImagePreview('sub-image-3', 'sub-image-3-preview');
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const row = button.closest('tr');
            const table = row.closest('table');
            const id = button.getAttribute('data-id');
            
            // Check if we're in the sales table by looking for saleCategory header
            const isSalesTable = Array.from(table.querySelectorAll('th')).some(
                header => header.textContent.includes('Category') && row.cells.length === 7
            );
            
            if (confirm('Are you sure you want to delete this item?')) {
                const formData = new FormData();
                
                // Set the appropriate form data key based on table type
                if (isSalesTable) {
                    formData.append('delete_sale_id', id);
                } else {
                    formData.append('delete_id', id);
                }

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        row.remove();
                        alert('Item deleted successfully!');
                    } else {
                        alert('Error deleting item: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting item. Please try again.');
                });
            }
        });
    });
});
    </script>

    <script>
document.querySelectorAll('.delete-order-btn').forEach(button => {
    button.addEventListener('click', function() {
        const row = button.closest('tr');
        const id = button.getAttribute('data-id');
        
        if (confirm('Are you sure you want to delete this order?')) {
            const formData = new FormData();
            formData.append('delete_order_id', id);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    row.remove();
                    alert('Order deleted successfully!');
                } else {
                    alert('Error deleting order: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting order. Please try again.');
            });
        }
    });
});
    </script>
</body>
</html>
