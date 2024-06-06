<?php
global $conn;
include '../includes/db.php';

session_start();
// get all questions by the ids passed in the request
$ids = $_GET['ids'];
$ids = explode(',', $ids);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$query = "SELECT * FROM questions WHERE id IN ($placeholders)";
$stmt = $conn->prepare($query);
$stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
$stmt->execute();
$result = $stmt->get_result();

$questions = array();
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}
//return the answers for each question
foreach ($questions as $key => $question) {
    $questionId = $question['id'];
    $query = "SELECT * FROM answers WHERE question_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $questionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $answers = array();
    while ($row = $result->fetch_assoc()) {
        $answers[] = $row;
    }
    $questions[$key]['answers'] = $answers;
}

echo json_encode($questions);
?>