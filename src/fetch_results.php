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

// Try to get test_id param from the request
$finished_exam_id = isset($_GET['test_id']) ? $_GET['test_id'] : $_SESSION['finished_exam_id'];
$user_id = $_SESSION['user_id'];

function duplicateQuestionDetails($finished_exam_id) {
    global $conn;
    $sql = "
        insert into question_details (question_id, timestamp, faculty_number, question_number, purpose, type, correct_answer,
                                      difficulty_level, feedback_correct, feedback_incorrect, remarks)
        select q.id, timestamp,
               faculty_number,
               question_number,
               purpose,
               type,
               correct_answer,
               difficulty_level,
               feedback_correct,
               feedback_incorrect,
               remarks
        from question_details
                 join questions on question_details.question_id = questions.id
                 join questions as q on q.description = questions.description
        where q.id != questions.id
          and q.id in (select question_id from finished_questions where exam_id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $finished_exam_id);
    $stmt->execute();
    $stmt->close();
}

function missingQuestionDetails($finished_exam_id) {
    global $conn;
    $sql = "
        select count(*) as cnt
        from question_details
                 join questions on question_details.question_id = questions.id
        where question_details.question_id in (select question_id from finished_questions where exam_id = ?)
          and (question_details.feedback_correct is null or question_details.feedback_incorrect is null)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $finished_exam_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['cnt'] > 0;
}

function getQuestionsAndAnswers($finished_exam_id, $user_id) {
    if (missingQuestionDetails($finished_exam_id)) {
        duplicateQuestionDetails($finished_exam_id);
    }
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
            qd.feedback_incorrect,
            r.rating,
            r.difficulty,
            r.time_taken,
            r.review
        FROM 
            finished_questions fq
        JOIN 
            questions q ON fq.question_id = q.id
        JOIN 
            answers a ON q.id = a.question_id
        LEFT OUTER JOIN
            question_details qd ON q.id = qd.question_id
        LEFT OUTER JOIN 
            reviews r ON r.question_id = q.id AND r.user_id = ?
        WHERE 
            fq.exam_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $finished_exam_id);
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
        $questions[$row['question_id']]['rating'] = $row['rating'];
        $questions[$row['question_id']]['difficulty'] = $row['difficulty'];
        $questions[$row['question_id']]['time_taken'] = $row['time_taken'];
        $questions[$row['question_id']]['review'] = $row['review'];
    }
    $stmt->close();
    return $questions;
}

$questions = getQuestionsAndAnswers($finished_exam_id, $user_id);
echo json_encode($questions);
?>
