<?php
global $conn;
include '../includes/db.php';
include '../includes/utilities.php';

// Start the session
session_start();

// Retrieve form data
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $hashed_password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($hashed_password)) {
        handleRegistrationError("Both username and password are required.");
    }

    // Check if user exists
    $sql = "SELECT id, password FROM users WHERE nickname=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify the password
        if ($hashed_password === $user['password']) {
            // Password is correct, so start a new session or resume the existing one
            $_SESSION['loggedIn'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $user['id'];
            // Redirect to index.html upon successful login
            header("Location: ../public/index.html");
        } else {
            handleRegistrationError("Invalid password");
        }
    } else {
        handleRegistrationError("Invalid username");
    }
} else {
    handleRegistrationError("No data received");
}

$conn->close();
?>
