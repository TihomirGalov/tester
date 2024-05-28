<?php
global $conn;
include '../includes/db.php'; // Include your database connection file
include '../includes/utilities.php';

// Start the session
session_start();

$usernameError = "";
$emailError = "";

// Retrieve form data
if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $hashed_password = $_POST['password']; // Already hashed password received from client-side

    // Validate input (server-side)
    if (empty($username) || empty($hashed_password) || empty($email)) {
        $usernameError = "Username, email, and password are required.";
    } else {
        // Check for existing username and email
        $sql = "SELECT * FROM users WHERE nickname = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                if ($row["nickname"] === $username) {
                    $usernameError = "Username already exists.";
                    handleRegistrationError($usernameError);
                }
                if ($row["email"] === $email) {
                    $emailError = "Email already exists.";
                    handleRegistrationError($emailError);

                }
            }
        }
    }

    // Insert user if no errors
    if (empty($usernameError) && empty($emailError)) {
        // Insert user into the database
        $sql = "INSERT INTO users (nickname, email, password) VALUES (?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        if ($stmt->execute() === TRUE) {
            $user_id = $conn->insert_id;
            // Set session variables to indicate user is logged in
            $_SESSION['username'] = $username;
            error_log("Logged in is set ");
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user_id;

            // Redirect to index.html upon successful registration
            header("Location: index.html");
            exit;
        } else {
            echo "Error: ". $stmt->error;
        }
    }
} else {
    echo "No data received";
}

$conn->close();
?>
