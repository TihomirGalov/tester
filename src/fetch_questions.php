<?php
global $conn;
include '../includes/db.php';

session_start();

$query = "SELECT * FROM questions";
$result = $conn->query($query);

$questions = array();
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

echo json_encode($questions);
?>