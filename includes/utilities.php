<?php
global $conn;
include 'db.php';

function updateUserInfo($conn, $username, $hashed_password, $faculty_number)
{
    $sql = "UPDATE users SET password=?, faculty_number=? WHERE nickname=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error;
        return;
    }
    $stmt->bind_param("sss", $hashed_password, $faculty_number, $username);
    if ($stmt->execute() === TRUE) {
        header("Location: ../public/index.html");
        exit;
    } else {
        echo "Error executing statement: " . $stmt->error;
    }
    $stmt->close();
}

// Function to handle errors and set HTTP response code
function handleRegistrationError($errorMessage)
{
    http_response_code(400);
    echo $errorMessage; // This will be displayed in the HTML form
    exit;
}

// Function to handle empty request data
function handleEmptyRequest()
{
    handleRegistrationError("No data received");
}

function getUserById($userId)
{
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    return $user;
}

function createTest($test_name, $questionsData, $createdBy)
{
    global $conn;

    // Create a new test
    $stmt = $conn->prepare("INSERT INTO tests (name, created_by) VALUES (?, ?)");
    $stmt->bind_param("si", $test_name, $createdBy);
    $stmt->execute();
    $testId = $stmt->insert_id;
    $stmt->close();

    // Prepare statements for inserting questions and answers
    $stmtQuestion = $conn->prepare("INSERT INTO questions (description, test_id) VALUES (?, ?)");
    $stmtAnswer = $conn->prepare("INSERT INTO answers (question_id, value, is_correct) VALUES (?, ?, ?)");
    $stmtQDetails = $conn->prepare("INSERT INTO question_details (question_id, faculty_number, question_number, purpose, type, correct_answer, difficulty_level, feedback_correct, feedback_incorrect, remarks, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $user = getUserById($createdBy);
    $questionIndex = 0;
    foreach ($questionsData as $questionData) {
        $question = $questionData['question'];
        $answers = $questionData['answers'];

        // Insert the question
        $stmtQuestion->bind_param("si", $question, $testId);
        $stmtQuestion->execute();
        $questionId = $stmtQuestion->insert_id;

        // Insert the answers
        $index = 1;
        $correctAnswer = 1;
        foreach ($answers as $answerData) {
            $answer = $answerData['answer'];
            $is_correct = $answerData['is_correct'];
            if ($is_correct == 1) {
                $correctAnswer = $index;
            }
            $stmtAnswer->bind_param("isi", $questionId, $answer, $is_correct);
            $stmtAnswer->execute();
            $index++;
        }

        // Insert the question details
        $questionNumber = $questionIndex + 1;
        $timestamp = date('Y-m-d H:i:s');
        echo json_encode($questionData);

        $stmtQDetails->bind_param("issssssssss",
            $questionId,
            $user['faculty_number'],
            $questionNumber,
            $questionData['question_purposes'][$questionIndex],
            $questionData['question_types'][$questionIndex],
            $correctAnswer,
            $questionData['difficulty_levels'][$questionIndex],
            $questionData['feedbacks_correct'][$questionIndex],
            $questionData['feedbacks_incorrect'][$questionIndex],
            $questionData['remarks'][$questionIndex],
            $timestamp
        );

        $stmtQDetails->execute();
        $questionIndex++;
    }

    $stmtQuestion->close();
    $stmtAnswer->close();

    return $testId;
}

function assignTest($testId, $users)
{
    global $conn;

    $stmt = $conn->prepare("INSERT INTO waiting_exams (waiting_due, test_id, user_id) VALUES (?, ?, ?)");

    foreach ($users as $user) {
        $waiting_due = date('Y-m-d H:i:s', strtotime('+1 day'));
        $userId = intval($user);
        $stmt->bind_param("sii", $waiting_due, $testId, $userId);
        $stmt->execute();
    }

    $stmt->close();
}

?>