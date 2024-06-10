<?php
session_start();
if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn'] || !$_SESSION['user_id']) {
    header("Location: ../public/login.html");
    exit();
}

include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $conn;
    $userId = $_SESSION['user_id'];
    $questionIds = $_POST['question_ids'];
    $reviews = $_POST['reviews'];
    $ratings = $_POST['ratings'];
    $difficulties = $_POST['difficulties'];
    $time_taken = $_POST['time_taken'];

    $stmt = $conn->prepare("INSERT INTO reviews (user_id, question_id, review, rating, difficulty, time_taken) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE review = VALUES(review), rating = VALUES(rating), difficulty = VALUES(difficulty), time_taken = VALUES(time_taken)");
    $stmt->bind_param("iisiii", $userId, $questionId, $review, $rating, $difficulty, $time_taken);

    foreach ($questionIds as $index => $questionId) {
        $review = $reviews[$index];
        $rating = $ratings[$index];
        $difficulty = $difficulties[$index];
        $time_taken = $time_taken[$index];
        echo "$review $rating $difficulty $time_taken";
        $stmt->execute();
        echo $stmt->error;
    }
    echo "Reviews saved successfully";


    $stmt->close();
    header("Location: ../public/index.html");
    exit();
}
?>
