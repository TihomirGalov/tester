<?php
global $conn;
include '../includes/db.php';

// Start the session
session_start();

// Ensure this PHP script is accessed via AJAX
if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['question_id'])) {
    http_response_code(400);
    echo json_encode(array("error" => "Invalid request"));
    exit();
}

// Fetch question_id from GET parameters
$question_id = $_GET['question_id'];

// Prepare the SQL statement to fetch creator details
$sql = "SELECT difficulty_level, purpose
        FROM question_details
        WHERE question_id = ?";

try {
    // Prepare and execute the SQL query
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $question_id);
    $stmt->execute();

    // Bind result variables
    $stmt->bind_result($difficulty_level, $purpose);

    // Fetch the result
    $stmt->fetch();

    // Check if result is found
    if ($difficulty_level !== null && $purpose !== null) {
        // Return JSON response with creator details
        echo json_encode(array(
            "difficulty_level" => $difficulty_level,
            "purpose" => $purpose
        ));
    } else {
        http_response_code(404);
        echo json_encode(array("error" => "Question details not found"));
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array("error" => "Database error: " . $e->getMessage()));
}
?>
