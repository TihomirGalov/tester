<?php
global $conn;
include 'db.php'; // Include your database connection file

// Function to handle errors and set HTTP response code
function handleRegistrationError($errorMessage) {
    http_response_code(400);
    echo $errorMessage; // This will be displayed in the HTML form
    exit;
}

// Start the session
session_start();

$usernameError = "";
$emailError = "";

// Retrieve form data
if (isset($_POST['username']) && isset($_POST['hashed_password']) && isset($_POST['email'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $hashed_password = $_POST['hashed_password']; // Already hashed password received from client-side

    // Validate input (server-side)
    if (empty($username) || empty($hashed_password) || empty($email)) {
        $usernameError = "Username, email, and password are required.";
    } else {
        // Check for existing username and email
        $sql = "SELECT * FROM user WHERE nickname = ? OR email = ?";
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
        $sql = "INSERT INTO user (nickname, email, password) VALUES (?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        if ($stmt->execute() === TRUE) {
            // Set session variables to indicate user is logged in
            $_SESSION['username'] = $username;
            error_log("Logged in is set ");
            $_SESSION['loggedin'] = true;

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
