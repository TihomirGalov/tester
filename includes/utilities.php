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

function handlePermissionError($errorMessage)
{
    http_response_code(403);
    echo $errorMessage;
    exit;
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

function checkUserPermission($userId) {
    //can_create_test
    $user = getUserById($userId);

    if ($user['can_create_test'] == 0) {
        handlePermissionError("You do not have permission to create a test.");
    }
}

function createTest($test_name, $questionsData, $createdBy)
{
    global $conn;
    checkUserPermission($createdBy);

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

function updateTestQuestions($testId, $testName, $questionsData, $userId)
{
    checkUserPermission($userId);
    global $conn;
    echo json_encode($questionsData);

    $stmtTest = $conn->prepare("UPDATE tests SET name = ? WHERE id = ?");
    $stmtTest->bind_param("si", $testName, $testId);
    $stmtQuestion = $conn->prepare("UPDATE questions SET description = ? WHERE id = ?");
    $stmtAnswer = $conn->prepare("UPDATE answers SET value = ?, is_correct = ? WHERE id = ?");
    $stmtQDetails = $conn->prepare("UPDATE question_details SET purpose = ?, type = ?, correct_answer = ?, difficulty_level = ?, feedback_correct = ?, feedback_incorrect = ?, remarks = ? WHERE question_id = ?");

    $questionIndex = 0;

    foreach ($questionsData as $questionData) {
        $questionId = $questionData['question_id'];
        $question = $questionData['question'];
        $answers = $questionData['answers'];

        $stmtQuestion->bind_param("si", $question, $questionId);
        $stmtQuestion->execute();

        $index = 1;
        $correctAnswer = 1;
        foreach ($answers as $answerData) {
            $answerId = $answerData['answer_id'];
            $answer = $answerData['answer'];
            $is_correct = $answerData['is_correct'];
            if ($is_correct == 1) {
                $correctAnswer = $index;
            }
            $stmtAnswer->bind_param("sii", $answer, $is_correct, $answerId);
            $stmtAnswer->execute();
            $index++;
        }

        $stmtQDetails->bind_param("ssissssi",
            $questionData['question_purposes'][$questionIndex],
            $questionData['question_types'][$questionIndex],
            $correctAnswer,
            $questionData['difficulty_levels'][$questionIndex],
            $questionData['feedbacks_correct'][$questionIndex],
            $questionData['feedbacks_incorrect'][$questionIndex],
            $questionData['remarks'][$questionIndex],
            $questionId
        );

        $stmtQDetails->execute();
        $questionIndex++;
    }

    $stmtTest->execute();
}

function deleteTest($testId, $userId) {
    checkUserPermission($userId);
    try {
        // Start the transaction
        global $conn;

        $conn->begin_transaction();

        // Delete from finished_questions
        $stmt = $conn->prepare("
            DELETE fq
            FROM finished_questions fq
            JOIN finished_exams fe ON fq.exam_id = fe.id
            WHERE fe.test_id = ?
        ");
        $stmt->bind_param('i', $testId);
        $stmt->execute();
        $stmt->close();

        // Delete from reviews
        $stmt = $conn->prepare("
            DELETE r
            FROM reviews r
            JOIN questions q ON r.question_id = q.id
            WHERE q.test_id = ?
        ");
        $stmt->bind_param('i', $testId);
        $stmt->execute();
        $stmt->close();

        // Delete from question_details
        $stmt = $conn->prepare("
            DELETE qd
            FROM question_details qd
            JOIN questions q ON qd.question_id = q.id
            WHERE q.test_id = ?
        ");
        $stmt->bind_param('i', $testId);
        $stmt->execute();
        $stmt->close();

        // Delete from answers
        $stmt = $conn->prepare("
            DELETE a
            FROM answers a
            JOIN questions q ON a.question_id = q.id
            WHERE q.test_id = ?
        ");
        $stmt->bind_param('i', $testId);
        $stmt->execute();
        $stmt->close();

        // Delete from questions
        $stmt = $conn->prepare("
            DELETE FROM questions
            WHERE test_id = ?
        ");
        $stmt->bind_param('i', $testId);
        $stmt->execute();
        $stmt->close();

        // Delete from finished_exams
        $stmt = $conn->prepare("
            DELETE FROM finished_exams
            WHERE test_id = ?
        ");
        $stmt->bind_param('i', $testId);
        $stmt->execute();
        $stmt->close();

        // Delete from waiting_exams
        $stmt = $conn->prepare("
            DELETE FROM waiting_exams
            WHERE test_id = ?
        ");
        $stmt->bind_param('i', $testId);
        $stmt->execute();
        $stmt->close();

        // Finally, delete the test itself
        $stmt = $conn->prepare("
            DELETE FROM tests
            WHERE id = ?
        ");
        $stmt->bind_param('i', $testId);
        $stmt->execute();
        $stmt->close();

        // Commit the transaction
        $conn->commit();
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        throw $exception;
    }
}

?>