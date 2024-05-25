<?php
global $conn;
include 'db.php';
include 'utilities.php';

// Start the session
session_start();

// Retrieve test answers
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

if (isset($json_obj['answers'])) {
    $test_id = $json_obj['test_id'];
    $answers = $json_obj['answers'];

    $score = 0;

    // Validate input
    if (empty($answers)) {
        handleEmptyRequest();
    }

    // Check if answers are correct
    $sql = 'SELECT * FROM answers WHERE id IN ('. implode(',', array_values($answers)) .')';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $answers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($answers[$row['question_id']] === $row['answer']) {
                $score++;
            }
        }
    }

    // Create new Finished Exam record
    //TODO add score in the finished_exams table
    $sql = 'INSERT INTO finished_exams (user_id, test_id) VALUES (?, ?)';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $_SESSION['user_id'], $test_id);
    $stmt->execute();
    $stmt->close();

    $exam_id = $conn->insert_id;

    // Create finished question records
    $sql = 'INSERT INTO finished_questions (exam_id, question_id, marked_answer) VALUES (?, ?, ?)';
    $stmt = $conn->prepare($sql);

    foreach ($answers as $marked_answer) {
        $stmt->bind_param('iii', $exam_id, $marked_answer['question_id'], $marked_answer['id']);
        $stmt->execute();
    }

    $stmt->close();
    // Store the score in the session
    $_SESSION['score'] = $score;
//    echo json_encode(['score' => $score]);
    // Redirect to the results page
    ob_end_flush();

    header("Location: results.html");
    exit;
} else {
    handleEmptyRequest("No data received");
}