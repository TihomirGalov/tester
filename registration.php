<?php
global $conn;
include 'db.php';

// Start the session
session_start();

// Retrieve form data
if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $hashed_password = $_POST['password']; // Already hashed password received from client-side

    // Validate input
    if (empty($username) || empty($hashed_password) || empty($email)) {
        echo "Username, email, and password are required.";
        exit;
    }

    // Insert user into database
    $sql = "INSERT INTO user (nickname, email, password) VALUES (?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    if ($stmt->execute() === TRUE) {
        // Redirect to index.html upon successful registration
        header("Location: index.html");
    } else {
        echo "Error: ". $stmt->error;
    }
} else {
    echo "No data received";
}

$conn->close();
?>
