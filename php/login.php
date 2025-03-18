<?php
session_start();

// Include the database connection
require_once "db.php";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Validate input
    if (empty($user_email) || empty($password)) {
        echo "Email and password are required.";
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
    } else {
        // Check for admin credentials
        if ($user_email === "admin@gmail.com" && $password === "admin") {
            $_SESSION['user_role'] = "admin";
            $_SESSION['user_email'] = $user_email;
            echo "<script>alert('Admin Login Successful');</script>";
            echo "<script>window.location.href='../dashboard.php';</script>";
            exit;
        }

        // Query the database for user credentials
        $stmt = $conn->prepare("SELECT id, full_name, password FROM user_registration WHERE user_email = ?");
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $full_name, $db_password);
            $stmt->fetch();

            // Verify the password using password_verify
            if (password_verify($password, $db_password)) {
                // Set session variables
                $_SESSION['user_role'] = "user";
                $_SESSION['user_id'] = $user_id;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['user_email'] = $user_email;

                echo "<script>alert('Login Successful');</script>";
                echo "<script>window.location.href='../Home.php';</script>";
                exit;
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No account found with this email.";
        }

        $stmt->close();
    }
}

$conn->close();
?>
