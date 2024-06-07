<?php
global $conn;
include '../includes/db.php';

$questionId = $_GET['id'];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$query = "SELECT qd.*, q.description, GROUP_CONCAT(a.value SEPARATOR '|') AS answers 
            FROM question_details qd 
            JOIN questions q ON qd.question_id = q.id 
            JOIN answers a ON qd.question_id = a.question_id 
            WHERE qd.question_id = ? 
            GROUP BY qd.id, q.description";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $questionId);
$stmt->execute();
$result = $stmt->get_result();

$fields = array();
while ($row = $result->fetch_assoc()) {
    $fields[] = array(
        'name' => 'description',
        'label' => 'Question',
        'type' => 'text',
        'value' => $row['description']
    );
    $answers = explode('|', $row['answers']);
    for ($i = 0; $i < count($answers); $i++) {
        $fields[] = array(
            'name' => "answer_" . ($i + 1),
            'label' => "Answer " . ($i + 1),
            'type' => 'text',
            'value' => $answers[$i]
        );
    }
    $fields[] = array(
        'name' => 'purpose',
        'label' => 'Purpose',
        'type' => 'text',
        'value' => $row['purpose']
    );
    $fields[] = array(
        'name' => 'type',
        'label' => 'Type',
        'type' => 'radio',
        'value' => $row['type'],
        'options' => 3
    );
    $fields[] = array(
        'name' => 'correct_answer',
        'label' => 'Correct Answer',
        'type' => 'radio',
        'value' => $row['correct_answer'],
        'options' => 4
    );
    $fields[] = array(
        'name' => 'difficulty_level',
        'label' => 'Difficulty Level',
        'type' => 'radio',
        'value' => $row['difficulty_level'],
        'options' => 5
    );
    $fields[] = array(
        'name' => 'feedback_correct',
        'label' => 'Feedback for Correct Answer',
        'type' => 'text',
        'value' => $row['feedback_correct']
    );
    $fields[] = array(
        'name' => 'feedback_incorrect',
        'label' => 'Feedback for Incorrect Answer',
        'type' => 'text',
        'value' => $row['feedback_incorrect']
    );
    $fields[] = array(
        'name' => 'remarks',
        'label' => 'Remarks',
        'type' => 'text',
        'value' => $row['remarks']
    );
}

echo json_encode($fields);
?>
