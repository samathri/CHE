<?php
include './php/db.php';
session_start();

// Handle delete request
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            padding: 20px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            white-space: nowrap;
        }
        @media (max-width: 768px) {
            .table td, .table th {
                min-width: 100px;
            }
            .table td:first-child, .table th:first-child {
                position: sticky;
                left: 0;
                background-color: #fff;
                z-index: 1;
            }
            .description-cell {
                max-width: 150px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .image-cell img {
                max-width: 40px;
                max-height: 40px;
            }
            .sub-images img {
                max-width: 25px;
                max-height: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>All Products</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        
        <div class="card">
            <div class="card-header">Product Details</div>
            <div class="card-body">
                <div class="table-responsive">
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
                            $sql = "SELECT id, productImage, productName, productCategory, productPrice, productDiscount, productDescription, productSize, quantity, subImage1, subImage2, subImage3 FROM products";
                            $result = mysqli_query($conn, $sql);

                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                    echo "<td class='image-cell'><img src='" . htmlspecialchars($row['productImage']) . "' alt='Product Image' style='width: 50px; height: 50px; object-fit: cover;'></td>";
                                    echo "<td>" . htmlspecialchars($row['productName']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['productCategory']) . "</td>";
                                    echo "<td>RS." . number_format($row['productPrice'], 2) . "</td>";
                                    echo "<td>RS." . number_format($row['productDiscount'], 2) . "</td>";
                                    echo "<td class='description-cell'>" . substr(htmlspecialchars($row['productDescription']), 0, 50) . "...</td>";
                                    echo "<td>" . htmlspecialchars($row['productSize'] ?? 'N/A') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['quantity'] ?? '0') . "</td>";
                                    echo "<td class='sub-images'>";
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
                                            <div class='action-buttons'>
                                                <a href='edit_product.php?id=" . $row['id'] . "' class='btn btn-success btn-sm'>Edit</a>
                                                <button class='btn btn-danger btn-sm delete-btn' data-id='" . $row['id'] . "'>Delete</button>
                                            </div>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='11' class='text-center'>No products found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const row = this.closest('tr');
                
                if (confirm('Are you sure you want to delete this product?')) {
                    const formData = new FormData();
                    formData.append('delete_id', productId);

                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            row.remove();
                            alert('Product deleted successfully!');
                        } else {
                            alert('Error deleting product: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error deleting product. Please try again.');
                    });
                }
            });
        });
    });
    </script>
</body>
</html>