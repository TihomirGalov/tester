<?php
global $conn;
include 'db.php';
include 'utilities.php';

// Start the session
session_start();

// Retrieve test answers
if (isset($_POST['answers'])) {
    $answers = $_POST['answers'];
    $score = 0;

    // Validate input
    if (empty($answers)) {
        handleEmptyRequest();
    }

    // Check if answers are correct
    $sql = 'SELECT * FROM answers WHERE question_id IN (". implode(",", array_keys($answers)) .")';
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($answers[$row['question_id']] === $row['answer']) {
                $score++;
            }
        }
    }

    // Store the score in the session
    $_SESSION['score'] = $score;
    // Redirect to the results page
    header("Location: results.php");
} else {
    handleEmptyRequest("No data received");
}