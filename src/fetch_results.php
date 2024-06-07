<?php
session_start();

if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn'] || !$_SESSION['user_id']) {
    header("Location: ../public/login.html");
    exit();
}

include '../includes/db.php';
include '../includes/utilities.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$finished_exam_id = $_SESSION['finished_exam_id'];
$user_id = $_SESSION['user_id'];

function getQuestionsAndAnswers($finished_exam_id, $user_id) {
    global $conn;
    $sql = "
        SELECT 
            fq.question_id, 
            q.description AS question,
            a.id AS answer_id, 
            a.value AS answer,
            a.is_correct,
            fq.marked_answer = a.id AS user_answer,
            qd.feedback_correct,
            qd.feedback_incorrect
        FROM 
            finished_questions fq
        JOIN 
            questions q ON fq.question_id = q.id
        JOIN 
            answers a ON q.id = a.question_id
        JOIN
            question_details qd ON q.id = qd.question_id
        WHERE 
            fq.exam_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $finished_exam_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $questions[$row['question_id']]['question'] = $row['question'];
        $questions[$row['question_id']]['feedback_correct'] = $row['feedback_correct'];
        $questions[$row['question_id']]['feedback_incorrect'] = $row['feedback_incorrect'];
        $questions[$row['question_id']]['answers'][] = [
            'answer_id' => $row['answer_id'],
            'answer' => $row['answer'],
            'is_correct' => $row['is_correct'],
            'user_answer' => $row['user_answer']
        ];
    }
    $stmt->close();
    return $questions;
}

$questions = getQuestionsAndAnswers($finished_exam_id, $user_id);
echo json_encode($questions);
?>
