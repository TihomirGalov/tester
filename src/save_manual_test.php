<?php
global $conn;
include '../includes/db.php';
include '../includes/utilities.php';

// Start the session
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['questions']) && isset($data['answers']) && isset($data['correct_answers'])) {
        $questions = $data['questions'];
        $answers = $data['answers'];
        $correctAnswers = $data['correct_answers'];

        $questionsData = [];

        for ($i = 0; $i < count($questions); $i++) {
            $question = $questions[$i];
            $questionAnswers = [];

            for ($j = 0; $j < 4; $j++) {
                $is_correct = ($j == $correctAnswers[$i]) ? 1 : 0;
                $questionAnswers[] = ['answer' => $answers[$i][$j], 'is_correct' => $is_correct];
            }

            $questionsData[] = ['question' => $question, 'answers' => $questionAnswers];
        }

        $createdBy = $_SESSION['user_id'];
        $testId = createTest($questionsData, $createdBy);

//        echo json_encode(['test_id' => $testId]);
        //TODO redirect does not work
        header("Location: index.html");
    } else {
        echo json_encode(['error' => 'Invalid input data.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
