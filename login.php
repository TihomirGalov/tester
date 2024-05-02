<?php
global $conn;
include 'db.php';

// Start the session
session_start();
session_regenerate_id(true);

// Retrieve form data
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        echo "Both username and password are required.";
        exit;
    }

    // Check if user exists
    $sql = "SELECT * FROM user WHERE nickname=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Password is correct, so start a new session or resume the existing one
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            // Redirect to index.html upon successful login
            header("Location: index.html");
        } else {
            echo "Invalid password";
        }
    } else {
        echo "Invalid username";
    }
} else {
    echo "No data received";
}

$conn->close();
?>
