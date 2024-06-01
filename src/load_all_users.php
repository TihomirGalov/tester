<?php
global $conn;
include '../includes/db.php';
include '../includes/utilities.php';

// Start the session
session_start();

//Select all users and return their names and ids
$sql = "SELECT id, nickname FROM users";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['users' => $users]);
?>