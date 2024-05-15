<?php
global $conn;
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

// Function to handle errors and set HTTP response code
function handleRegistrationError($errorMessage) {
    http_response_code(400);
    echo $errorMessage; // This will be displayed in the HTML form
    exit;
}