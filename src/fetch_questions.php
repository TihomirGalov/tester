<?php
global $conn;
include '../includes/db.php'; // Include your database connection script

session_start();

$query = "
    SELECT t.name AS test_name, questions.id, questions.description, 
           COALESCE(AVG(reviews.rating), 0) AS rating 
    FROM questions
    LEFT JOIN reviews ON questions.id = reviews.question_id
    JOIN tests AS t ON questions.test_id = t.id
    GROUP BY questions.id, t.name 
    ORDER BY rating DESC";

$result = $conn->query($query);

$questions = array();
while ($row = $result->fetch_assoc()) {
    $row['rating'] = (float)$row['rating']; // Ensure rating is a float
    $questions[] = $row;
}

echo json_encode($questions);
?>
