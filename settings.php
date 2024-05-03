<?php
global $conn;
session_start();
include 'db.php';

function updateUserInfo($conn, $username, $email, $hashed_password) {
    $sql = "INSERT INTO user (nickname, email, password) VALUES (?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    if ($stmt->execute() === TRUE) {
        header("Location: index.html");
        exit;
    } else {
        echo "Error: ". $stmt->error;
    }
}

if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['current_password'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $current_password = $_POST['current_password'];
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : null;

    // If the user provided a new password, update it
    if (!empty($new_password)) {
        //TODO current_password must be hashed
        updateUserInfo($conn, $username, $email, $current_password);
    } else {
        //TODO new_password must be hashed
        updateUserInfo($conn, $username, $email, $new_password);
    }

    header("Location: index.html");
    exit;
} else {
    // Redirect back to the settings page with an error message
    header("Location: settings.html?error=1");
    exit;
}
?>
