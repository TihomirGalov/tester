<?php
global $conn;
include '../includes/db.php';
include '../includes/utilities.php';

// Start the session
session_start();

// Retrieve test answers
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

if (isset($json_obj['answers'])) {
    $test_id = $json_obj['test_id'];
    $time_taken = $json_obj['time_taken'];
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
    $correctAnswers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Compare user answers with correct answers
    foreach ($correctAnswers as $correctAnswer) {
        if ($correctAnswer['is_correct'] == 1) {
            $score++;
        }
    }

    // Create new Finished Exam record
    $sql = 'INSERT INTO finished_exams (user_id, test_id, time_taken) VALUES ( ?, ?, ?)';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $_SESSION['user_id'], $test_id, $time_taken);
    $stmt->execute();
    $stmt->close();

    $exam_id = $conn->insert_id;

    // Create finished question records
    $sql = 'INSERT INTO finished_questions (exam_id, question_id, marked_answer) VALUES (?, ?, ?)';
    $stmt = $conn->prepare($sql);

    foreach ($answers as $question_id => $selected_answer) {
        $stmt->bind_param('iii', $exam_id, $question_id, $selected_answer);
        $stmt->execute();
    }

    $stmt->close();

    // Store the score in the session
    $_SESSION['score'] = $score;
    $_SESSION['finished_exam_id'] = $exam_id;
    // Redirect to the results page
    header("Location: ../public/results.html", true, 302);
    exit;
} else {
    handleEmptyRequest("No data received");
}
