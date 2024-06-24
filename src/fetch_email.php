<?php
global $conn;

include '../includes/db.php';

session_start();

if (isset($_POST['username'])) {
    $username = $_POST['username'];

    $sql = "SELECT email FROM users WHERE nickname = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['error' => 'SQL prepare statement failed']);
        exit;
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();

    if ($email) {
        echo json_encode(['email' => $email]);
    } else {
        echo json_encode(['error' => 'Email not found']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'Username not provided']);
}
?>
