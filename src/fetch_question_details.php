<?php
session_start();
global $conn;
include '../includes/db.php';

$questionId = $_GET['id'];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$query = "SELECT qd.*, q.description, t.created_by AS creator_id, GROUP_CONCAT(a.value SEPARATOR '|') AS answers 
            FROM question_details qd 
            JOIN questions q ON qd.question_id = q.id 
            JOIN answers a ON qd.question_id = a.question_id 
            JOIN tests t ON q.test_id = t.id
            WHERE qd.question_id = ? 
            GROUP BY qd.id, q.description, t.created_by";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $questionId);
$stmt->execute();
$result = $stmt->get_result();

$fields = array();
$created_by = null;
while ($row = $result->fetch_assoc()) {
    $fields[] = array(
        'name' => 'description',
        'label' => 'Въпрос',
        'type' => 'text',
        'value' => $row['description']
    );
    $answers = explode('|', $row['answers']);
    for ($i = 0; $i < count($answers); $i++) {
        $fields[] = array(
            'name' => "answer_" . ($i + 1),
            'label' => "Отговор " . ($i + 1),
            'type' => 'text',
            'value' => $answers[$i]
        );
    }
    $fields[] = array(
        'name' => 'purpose',
        'label' => 'Цел',
        'type' => 'text',
        'value' => $row['purpose']
    );
    $fields[] = array(
        'name' => 'type',
        'label' => 'Тип',
        'type' => 'radio',
        'value' => $row['type'],
        'options' => 3
    );
    $fields[] = array(
        'name' => 'correct_answer',
        'label' => 'Верен отговор',
        'type' => 'radio',
        'value' => $row['correct_answer'],
        'options' => 4
    );
    $fields[] = array(
        'name' => 'difficulty_level',
        'label' => 'Трудност на въпроса',
        'type' => 'radio',
        'value' => $row['difficulty_level'],
        'options' => 5
    );
    $fields[] = array(
        'name' => 'feedback_correct',
        'label' => 'Обратна връзка при правилен отговор',
        'type' => 'text',
        'value' => $row['feedback_correct']
    );
    $fields[] = array(
        'name' => 'feedback_incorrect',
        'label' => 'Обратна връзка при грешен отговор',
        'type' => 'text',
        'value' => $row['feedback_incorrect']
    );
    $fields[] = array(
        'name' => 'remarks',
        'label' => 'Забележка',
        'type' => 'text',
        'value' => $row['remarks']
    );

    $created_by = $row['creator_id'];
}
$stmt->close();

$aggQuery = "SELECT AVG(rating) AS rating, AVG(difficulty) as difficulty, AVG(time_taken) as time_taken FROM reviews WHERE question_id = ? GROUP BY question_id;";
$reviewsQuery = "SELECT review FROM reviews WHERE question_id = ?;";

$stmt = $conn->prepare($aggQuery);
$stmt->bind_param('i', $questionId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $fields[] = array(
        'name' => 'rating',
        'label' => 'Средна оценка',
        'type' => 'text',
        'disabled' => 'false',
        'value' => $row['rating']
    );
    $fields[] = array(
        'name' => 'difficulty',
        'label' => 'Средна трудност',
        'type' => 'text',
        'disabled' => 'false',
        'value' => $row['difficulty']
    );
    $fields[] = array(
        'name' => 'time_taken',
        'label' => 'Средно време за решаване',
        'type' => 'text',
        'disabled' => 'false',
        'value' => $row['time_taken']
    );
}

$stmt->close();

$stmt = $conn->prepare($reviewsQuery);
$stmt->bind_param('i', $questionId);
$stmt->execute();
$result = $stmt->get_result();

$reviews = array();
while ($row = $result->fetch_assoc()) {
    $reviews[] = $row['review'];
}
$fields[] = array(
    'name' => 'reviews',
    'label' => 'Отзиви',
    'type' => 'array',
    'value' => $reviews
);

$response = array(
    'fields' => $fields,
    'creator_id' => $created_by
);

echo json_encode($response);
?>
