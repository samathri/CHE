<?php
// Database connection
require_once "db.php";

// Start session to prevent duplicate submissions
session_start();

// Initialize response array
$response = array(
    'success' => false,
    'message' => '',
    'redirect' => ''
);

// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if this is a duplicate submission
    if (isset($_SESSION['last_submission']) && time() - $_SESSION['last_submission'] < 5) {
        $response['message'] = "Please wait a few seconds before trying again.";
        echo json_encode($response);
        exit;
    }
    
    // Store submission time
    $_SESSION['last_submission'] = time();
    
    // Get and sanitize input data
    $full_name = trim(filter_var($_POST["name"], FILTER_SANITIZE_STRING));
    $user_email = trim(filter_var($_POST["email"], FILTER_SANITIZE_EMAIL));
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    
    // Validation
    if (empty($full_name) || empty($user_email) || empty($password) || empty($confirm_password)) {
        $response['message'] = "All fields are required.";
    } 
    elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Invalid email format.";
    }
    elseif ($password !== $confirm_password) {
        $response['message'] = "Passwords do not match.";
    }
    else {
        // Use transaction to ensure data consistency
        $conn->begin_transaction();
        
        try {
            // Check if email exists
            $check_email = $conn->prepare("SELECT user_email FROM user_registration WHERE user_email = ? LIMIT 1");
            $check_email->bind_param("s", $user_email);
            $check_email->execute();
            $result = $check_email->get_result();
            
            if ($result->num_rows > 0) {
                $response['message'] = "Email already exists. Please use a different email.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO user_registration (full_name, user_email, password, confirm_password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $full_name, $user_email, $hashed_password, $hashed_password);
                
                if ($stmt->execute()) {
                    $conn->commit();
                    $response['success'] = true;
                    $response['message'] = "Account created successfully!";
                    $response['redirect'] = 'login.html';
                } else {
                    throw new Exception("Error inserting data");
                }
                $stmt->close();
            }
            $check_email->close();
            
        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = "Error: " . $e->getMessage();
        }
    }
} else {
    $response['message'] = "Invalid request method.";
}

$conn->close();

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>