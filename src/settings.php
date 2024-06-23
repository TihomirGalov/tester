<?php
global $conn;
session_start();
include '../includes/db.php';
include '../includes/utilities.php';

$username = $_POST['username'];
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

    // Handle password update
    if (isset($_POST['new_password']) && isset($_POST['current_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];

        // Check if new password is provided
        if (!empty($new_password)) {
            // Verify the current password
            if ($current_password !== $hashed_password) {
                header("Location: ../public/settings.html?error=incorrect_password");
                exit;
            }
            // Update hashed password
            $hashed_password = $new_password;
        }
    }

    // Validate and update faculty_number
    if (!empty($faculty_number)) {
        // Check if faculty_number already exists
        $sql = "SELECT * FROM users WHERE faculty_number=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $faculty_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            header("Location: ../public/settings.html?error=faculty_number_exists");
            exit;
        }
    }

    updateUserInfo($conn, $username, $hashed_password, $faculty_number);
}

?>
