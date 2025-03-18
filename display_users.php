<?php
include './php/db.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Users</title>
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
            <h1>All Users</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        
        <div class="card">
            <div class="card-header">User Details</div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>User Name</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Company</th>
                    <th>Address1</th>
                    <th>Address2</th>
                    <th>City</th>
                    <th>Country</th>
                    <th>Province</th>
                    <th>Zip</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                // Fetch only 3 users from database
                $sql = "SELECT user_registration.id,user_registration.full_name,user_registration.user_email,user_address.firstName,user_address.lastName,user_address.phone,user_address.company,user_address.address1,user_address.address2,user_address.city,user_address.country,user_address.province,user_address.zip FROM user_registration 
                LEFT JOIN user_address
                ON user_registration.id = user_address.user_id";
                
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['firstName']) . " " . htmlspecialchars($row['lastName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['user_email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['company']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['address1']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['address2']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['city']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['country']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['province']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['zip']) . "</td>";
                        echo "</tr>";
                        
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No users found</td></tr>";
                }
                ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>