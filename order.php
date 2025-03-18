<?php 
include './php/db.php'; 
session_start(); 

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $sql = "DELETE FROM orders WHERE id = '$order_id'";
    
    $response = ['success' => false];
    if (mysqli_query($conn, $sql)) {
        $response['success'] = true;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Orders</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>All Orders</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        
        <div class="card">
            <div class="card-header">Order Details</div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User Name</th>
                            <th>Product Name</th>
                            <th>Size</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Order Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $sql = "
                        SELECT
                            orders.id,
                            orders.user_id,
                            orders.size,
                            orders.quantity,
                            orders.price,
                            orders.total,
                            orders.order_date,
                            products.productName
                        FROM orders
                        INNER JOIN products ON orders.product_id = products.id
                    ";
                    $result = mysqli_query($conn, $sql);
                    
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['productName']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['size']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['price']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['total']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['order_date']) . "</td>";
                            echo "<td>
                                <button class='btn btn-danger btn-sm' onclick='deleteOrder(" . $row['id'] . ")'>Delete</button>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9' class='text-center'>No orders found</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteOrder(orderId) {
            if (confirm('Are you sure you want to delete this order?')) {
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'order_id=' + orderId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order deleted successfully!');
                        // Remove the row from the table
                        const row = document.querySelector(`button[onclick="deleteOrder(${orderId})"]`).closest('tr');
                        row.remove();
                    } else {
                        alert('Error deleting order');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting order');
                });
            }
        }
    </script>
</body>
</html>