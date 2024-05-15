<?php
global $conn;
session_start();
include 'db.php';

function updateUserInfo($conn, $username, $hashed_password)
{
    $sql = "UPDATE users SET password = ? WHERE nickname = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error;
        return;
    }
    $stmt->bind_param("ss", $hashed_password, $username);
    if ($stmt->execute() === TRUE) {
        header("Location: index.html");
        exit;
    } else {
        echo "Error executing statement: " . $stmt->error;
    }
    $stmt->close();
}


$username = $_POST['username'];
$email = $_POST['email'];

// Fetch the current hashed password from the database
$sql = "SELECT password FROM users WHERE nickname=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    // Verify the password
    if (isset($_POST['current_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : null;
        if ($current_password === $user['password']) {
            if (!empty($new_password)) {
                $hashed_password = $new_password;
            } else {
                $hashed_password = $user['password'];
            }

            updateUserInfo($conn, $username, $hashed_password);
        } else {
            header("Location: settings.html?error=incorrect_password");
            exit;
        }
    }

    updateUserInfo($conn, $username, $user['password']);
}

?>
