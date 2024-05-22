<?php
global $conn;
include 'db.php';
include 'utilities.php';

// Start the session
session_start();

// Get questions for the test id
if (isset($_GET['test_id'])) {
    $test_id = $_GET['test_id'];

    //$sql = "SELECT * FROM questions WHERE test_id = ?";
    $sql = "SELECT * FROM questions WHERE test_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $test_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Retrieve answers for the questions
    $sql = "SELECT question_id, id, value as data FROM answers WHERE question_id IN (". implode(",", array_column($questions, 'id')) .")";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $answers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Return {questions: [{questionId: 1, question: "What is?", answers:[{id: 1, answer: "A"}]}]}
    $response = [];
    foreach ($questions as $question) {
        $response[] = [
            'questionId' => $question['id'],
            'question' => $question['description'],
            'answers' => array_values(array_filter($answers, function($answer) use ($question) {
                return $answer['question_id'] == $question['id'];
            }))
        ];
    }

    echo json_encode(['questions' => $response]);
} else {
    handleEmptyRequest();
}