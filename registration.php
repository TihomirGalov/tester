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

    // Hash the password using PHP's built-in function
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database
    $sql = "INSERT INTO users (username, password) VALUES (?,?)";
    //TODO conn is undefined (not correctly imported from db.php)
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $hashed_password);
    if ($stmt->execute() === TRUE) {
        echo "Registration successful";
    } else {
        echo "Error: ". $stmt->error;
    }
} else {
    echo "No data received";
}

$conn->close();
?>