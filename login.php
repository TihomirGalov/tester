<?php
include 'db.php';

// Retrieve form data
$username = $_POST['username'];
$password = $_POST['password'];

// Check if user exists
$sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
//TODO undefined variable conn
//$result = $conn->query($sql);
//
//if ($result->num_rows > 0) {
//    echo "Login successful";
//} else {
//    echo "Invalid username or password";
//}
//
//$conn->close();
?>
