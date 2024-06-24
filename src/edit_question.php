<?php
global $conn;
include '../includes/db.php';

// Check if question ID is provided in the URL
if (!isset($_GET['id'])) {
    echo 'Question ID is required.';
    exit;
}

$questionId = $_GET['id'];

// Fetch question details from the database
$query = "SELECT * FROM questions WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $questionId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo 'Question not found.';
    exit;
}

$question = $result->fetch_assoc();
?>