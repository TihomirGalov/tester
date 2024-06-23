<?php
global $conn;
include '../includes/db.php'; // Include your database connection file
include '../includes/utilities.php';

// Start the session
session_start();

$usernameError = "";
$emailError = "";
$facultyNumberError = "";

// Retrieve form data
if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email']) && isset($_POST['faculty_number'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $hashed_password = $_POST['password']; // Already hashed password received from client-side
    $faculty_number = $_POST['faculty_number'];

    // Validate input (server-side)
    if (empty($username) || empty($hashed_password) || empty($email) || empty($faculty_number)) {
        $registrationError = "Username, email, password, and faculty number are required.";
        echo json_encode(array("error" => $registrationError));
        exit;
    } else {
        // Check for existing username and email
        $sql = "SELECT * FROM users WHERE nickname = ? OR email = ? or faculty_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $faculty_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                if ($row["nickname"] === $username) {
                    $usernameError = "Username already exists.";
                    echo json_encode(array("field" => "username", "error" => $usernameError));
                    exit;
                }
                if ($row["email"] === $email) {
                    $emailError = "Email already exists.";
                    echo json_encode(array("field" => "email", "error" => $emailError));
                    exit;
                }
                if ($row["faculty_number"] === $faculty_number) {
                    $facultyNumberError = "Faculty number already exists.";
                    echo json_encode(array("field" => "faculty_number", "error" => $facultyNumberError));
                    exit;
                }
            }
        }
    }

    // Insert user if no errors
    if (empty($usernameError) && empty($emailError)) {
        // Insert user into the database
        $sql = "INSERT INTO users (nickname, email, password, faculty_number) VALUES (?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $faculty_number);
        if ($stmt->execute() === TRUE) {
            $user_id = $conn->insert_id;
            // Set session variables to indicate user is logged in
            $_SESSION['username'] = $username;
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user_id;

            // Respond with success message
            echo json_encode(array("success" => "User registered successfully."));
            exit;
        } else {
            echo json_encode(array("error" => "Error registering user."));
            exit;
        }
    }
} else {
    echo json_encode(array("error" => "No data received"));
    exit;
}

$conn->close();
?>
