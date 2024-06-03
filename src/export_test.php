<?php
global $conn;
include '../includes/db.php';

if (!isset($_GET['test_id'])) {
    die('Test ID not provided');
}

$test_id = $_GET['test_id'];

// Fetch test details
$query = $conn->prepare("SELECT name FROM tests WHERE id = ?");
$query->bind_param("i", $test_id);
$query->execute();
$result = $query->get_result();
$test = $result->fetch_assoc();

if (!$test) {
    die('Test not found');
}

$test_name = $test['name'];

// Fetch test questions and answers
$query = $conn->prepare("
    SELECT 
        q.id as question_id, 
        q.description, 
        a.id as answer_id, 
        a.value, 
        a.is_correct 
    FROM 
        questions q 
    LEFT JOIN 
        answers a 
    ON 
        q.id = a.question_id 
    WHERE 
        q.test_id = ?
");
$query->bind_param("i", $test_id);
$query->execute();
$result = $query->get_result();

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[$row['question_id']]['description'] = $row['description'];
    $questions[$row['question_id']]['answers'][] = [
        'answer_id' => $row['answer_id'],
        'answer' => $row['value'],
        'is_correct' => $row['is_correct']
    ];
}

// Create CSV content
$csv_content = "Question,Answer,Is Correct\n";

foreach ($questions as $question) {
    foreach ($question['answers'] as $answer) {
        $csv_content .= "\"{$question['description']}\",\"{$answer['answer']}\",\"{$answer['is_correct']}\"\n";
    }
}

// Send CSV as a download
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="' . $test_name . '.csv"');
echo $csv_content;
exit;
?>
