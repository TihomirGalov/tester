<?php
session_start();
include 'db.php'; // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['username']) || $_SESSION['loggedIn']) {
    // Redirect the user to the login page if they are not logged in
    header("Location: login.html");
    exit();
}

// Fetch user's information from the database
$username = $_SESSION['username'];
$sql = "SELECT * FROM users WHERE nickname = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $currentUsername = $user['nickname'];
    $currentEmail = $user['email'];
} else {
// Handle the case where user information is not found in the database
// For example, redirect the user to an error page
    header("Location: error.html");
    exit;
}

// Get data from the POST request and update it

?>