<?php

include '../includes/db.php';
include '../includes/utilities.php';

// Start the session
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $testId = $_GET['test_id'];
    if (isset($testId)) {
        $userId = $_SESSION['user_id'];
        deleteTest($testId, $userId);

        header("Location: ../public/index.html");
    } else {
        echo json_encode(['error' => 'Invalid input data.']);
    }

} else {
    echo json_encode(['error' => 'Invalid request method.']);
}