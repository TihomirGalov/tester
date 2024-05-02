<?php
include 'db.php';

// Start the session
session_start();

// Retrieve form data
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        echo "Both username and password are required.";
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if user exists
    $sql = "SELECT * FROM User WHERE nickname=?";
    //TODO conn is undefined (not correctly imported from db.php)
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
            echo "Login successful";
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