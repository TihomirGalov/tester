<?php
global $conn;
session_start();
include '../includes/db.php';
include '../includes/utilities.php';

$username = $_POST['username'];
$email = $_POST['email'];
$faculty_number = $_POST['faculty_number'];


// Fetch the current hashed password from the database
$sql = "SELECT password FROM users WHERE nickname=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $hashed_password = $user['password'];
    if (isset($_POST['new_password']) && isset($_POST['current_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];

        // Check if new password is provided
        if (!empty($new_password)) {
            // Verify the current password
            if ($current_password === $user['password']) {
                $hashed_password = $new_password;
            } else {
                header("Location: ../public/settings.html?error=incorrect_password");
                exit;
            }
        }
    }

    updateUserInfo($conn, $username, $hashed_password, $faculty_number);
}
?>
