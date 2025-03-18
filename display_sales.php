<?php
include './php/db.php';
session_start();

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['delete_id']);
    
    $sql = "SELECT saleImage FROM sale_products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    
    $delete_sql = "DELETE FROM sale_products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        if (!empty($product['saleImage'])) unlink($product['saleImage']);
        
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
                $sql = "SELECT * FROM sale_products ";
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
                
                if (confirm('Are you sure you want to delete this sale product?')) {
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