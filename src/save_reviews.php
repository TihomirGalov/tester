global$conn; global$conn; // src/save_reviews.php
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

    $stmt = $conn->prepare("INSERT INTO reviews (user_id, question_id, review) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE review = VALUES(review)");
    $stmt->bind_param("iis", $userId, $questionId, $review);

    foreach ($questionIds as $index => $questionId) {
        $review = $reviews[$index];
        $stmt->execute();
    }

    $stmt->close();
    header("Location: ../public/index.html");
    exit();
}
?>
