<?php
global $conn;
session_start();

if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn'] || !$_SESSION['user_id']) {
    header("Location: ../public/login.html");
    exit();
}

include '../includes/db.php';

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

// Update answers table
$sql = "DELETE FROM answers WHERE question_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Error preparing statement for delete answers: " . $conn->error);
    echo json_encode(["status" => "error", "message" => "Database error"]);
    exit();
}

$stmt->bind_param("i", $question_id);

if (!$stmt->execute()) {
    error_log("Error executing statement for delete answers: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Database error"]);
    exit();
}

$stmt->close();

$sql = "INSERT INTO answers (question_id, value, is_correct) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Error preparing statement for insert answers: " . $conn->error);
    echo json_encode(["status" => "error", "message" => "Database error"]);
    exit();
}

foreach ($answers as $key => $value) {
    $answer_id = str_replace('answer_', '', $key);
    $is_correct = ($answer_id == $correct_answer) ? 1 : 0;
    $stmt->bind_param("isi", $question_id, $value, $is_correct);
    if (!$stmt->execute()) {
        error_log("Error executing statement for insert answers: " . $stmt->error);
        echo json_encode(["status" => "error", "message" => "Database error"]);
        exit();
    }
}

$stmt->close();

$conn->close();

error_log("Question details updated successfully for question_id: " . $question_id);
echo json_encode(["status" => "success"]);
?>
