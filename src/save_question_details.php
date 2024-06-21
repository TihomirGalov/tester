<?php
global $conn;
session_start();

if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn'] || !$_SESSION['user_id']) {
    header("Location: ../public/login.html");
    exit();
}

include '../includes/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Collect data from POST request
$question_id = $_POST['id'];
$purpose = $_POST['purpose'];
$type = $_POST['type'];
$correct_answer = $_POST['correct_answer'];
$difficulty_level = $_POST['difficulty_level'];
$feedback_correct = $_POST['feedback_correct'];
$feedback_incorrect = $_POST['feedback_incorrect'];
$remarks = $_POST['remarks'];
$description = $_POST['description'];
$answers = array_filter($_POST, function($key) {
    return strpos($key, 'answer_') === 0;
}, ARRAY_FILTER_USE_KEY);

// Log POST data for debugging
error_log("Collected POST data: " . print_r($_POST, true));

// Update question_details table
$sql = "
    UPDATE question_details SET 
        purpose = ?, 
        type = ?, 
        correct_answer = ?, 
        difficulty_level = ?, 
        feedback_correct = ?, 
        feedback_incorrect = ?, 
        remarks = ? 
    WHERE question_id = ?
";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Error preparing statement for question_details: " . $conn->error);
    echo json_encode(["status" => "error", "message" => "Database error"]);
    exit();
}

// Corrected bind_param: 8 parameters in both type string and variables
$stmt->bind_param(
    "siiisssi",
    $purpose,
    $type,
    $correct_answer,
    $difficulty_level,
    $feedback_correct,
    $feedback_incorrect,
    $remarks,
    $question_id
);

if (!$stmt->execute()) {
    error_log("Error executing statement for question_details: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Database error"]);
    exit();
}

$stmt->close();

// Update questions table
$sql = "UPDATE questions SET description = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Error preparing statement for questions: " . $conn->error);
    echo json_encode(["status" => "error", "message" => "Database error"]);
    exit();
}

$stmt->bind_param("si", $description, $question_id);

if (!$stmt->execute()) {
    error_log("Error executing statement for questions: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Database error"]);
    exit();
}

$stmt->close();

// Get the minimum answer_id associated with the current question_id
$sql_min_id = "SELECT MIN(id) AS min_id FROM answers WHERE question_id = ?";
$stmt_min_id = $conn->prepare($sql_min_id);
$stmt_min_id->bind_param("i", $question_id);
$stmt_min_id->execute();
$stmt_min_id->bind_result($min_id);
$stmt_min_id->fetch();
$stmt_min_id->close();

// Use the minimum answer_id as a base for incrementing in each iteration
$current_answer_id = $min_id;

foreach ($answers as $key => $value) {
    error_log("Correct answer is: $correct_answer for question with id $question_id");
    // Prepare the update query for the current answer
    $sql_update = "UPDATE answers SET value = ?, is_correct = ? WHERE id = ? AND question_id = ?";
    $stmt_update = $conn->prepare($sql_update);

    // Determine the correctness of the current answer
    $is_correct = (($current_answer_id % 4) == $correct_answer) ? 1 : 0;

    // Bind parameters and execute the update query
    $stmt_update->bind_param("siii", $value, $is_correct, $current_answer_id, $question_id);
    $stmt_update->execute();

    // Check for errors and handle them if any
    if ($stmt_update->error) {
        error_log("Error executing statement for updating answer with ID $current_answer_id: " . $stmt_update->error);
        echo json_encode(["status" => "error", "message" => "Database error"]);
        exit();
    }

    // Close the statement after each iteration
    $stmt_update->close();

    // Increment the current_answer_id for the next iteration
    $current_answer_id++;
}

$conn->close();

error_log("Question details updated successfully for question_id: " . $question_id);
echo json_encode(["status" => "success"]);
?>
?>
