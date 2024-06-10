<?php
global $conn;
include '../includes/db.php';

session_start();

$query = "SELECT questions.id, questions.description, AVG(rating) as rating FROM questions left outer join reviews on questions.id = reviews.question_id GROUP BY questions.id order by AVG(rating) desc";
$result = $conn->query($query);

$questions = array();
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

echo json_encode($questions);
?>