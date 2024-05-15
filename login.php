<?php
global $conn;
include 'db.php';

function handleRegistrationError($errorMessage) {
    http_response_code(400);
    echo $errorMessage; // This will be displayed in the HTML form
    exit;
}

// Start the session
session_start();

// Retrieve form data
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $hashed_password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($hashed_password)) {
        handleRegistrationError("Both username and password are required.");
    }

    // Check if user exists
    $sql = "SELECT password FROM users WHERE nickname=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify the password
        if ($hashed_password === $user['password']) {
            // Password is correct, so start a new session or resume the existing one
            $_SESSION['loggedIn'] = true;
            $_SESSION['username'] = $username;
            // Redirect to index.html upon successful login
            header("Location: index.html");
        } else {
            handleRegistrationError("Invalid password");
        }
    } else {
        handleRegistrationError("Invalid username");
    }
} else {
    handleRegistrationError("No data received");
}

$conn->close();
?>
