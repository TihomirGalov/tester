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

// Function to handle empty request data
function handleEmptyRequest() {
    handleRegistrationError("No data received");
}

function createTest($test_name, $questionsData, $createdBy) {
    global $conn;

    // Create a new test
    $stmt = $conn->prepare("INSERT INTO tests (name, created_by) VALUES (?, ?)");
    $stmt->bind_param("si", $test_name,$createdBy);
    $stmt->execute();
    $testId = $stmt->insert_id;
    $stmt->close();

    // Prepare statements for inserting questions and answers
    $stmtQuestion = $conn->prepare("INSERT INTO questions (description, test_id) VALUES (?, ?)");
    $stmtAnswer = $conn->prepare("INSERT INTO answers (question_id, value, is_correct) VALUES (?, ?, ?)");

    foreach ($questionsData as $questionData) {
        $question = $questionData['question'];
        $answers = $questionData['answers'];

        // Insert the question
        $stmtQuestion->bind_param("si", $question, $testId);
        $stmtQuestion->execute();
        $questionId = $stmtQuestion->insert_id;

        // Insert the answers
        foreach ($answers as $answerData) {
            $answer = $answerData['answer'];
            $is_correct = $answerData['is_correct'];
            $stmtAnswer->bind_param("isi", $questionId, $answer, $is_correct);
            $stmtAnswer->execute();
        }
    }

    $stmtQuestion->close();
    $stmtAnswer->close();

    return $testId;
}
?>