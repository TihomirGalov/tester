<?php
include 'db.php';

// Retrieve form data
$username = $_POST['username'];
$password = $_POST['password'];

// Insert user into database
$sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
//TODO undefined variable conn
//if ($conn->query($sql) === TRUE) {
//    echo "Registration successful";
//} else {
//    echo "Error: " . $sql . "<br>" . $conn->error;
//}
//
//$conn->close();
?>
