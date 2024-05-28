<?php
global $conn;
include 'db.php';
include 'utilities.php';

// Start the session
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['questions']) && isset($data['answers']) && isset($data['correctAnswers'])) {
        $questions = $data['questions'];
        $answers = $data['answers'];
        $correctAnswers = $data['correctAnswers'];

        $questionsData = [];
        $answerIndex = 0;

        for ($i = 0; $i < count($questions); $i++) {
            $question = $questions[$i];
            $questionAnswers = [];

            for ($j = 0; $j < 4; $j++) {
                $is_correct = ($j == $correctAnswers[$i]) ? 1 : 0;
                $questionAnswers[] = ['answer' => $answers[$answerIndex], 'is_correct' => $is_correct];
                $answerIndex++;
            }

            $questionsData[] = ['question' => $question, 'answers' => $questionAnswers];
        }

        $createdBy = $_SESSION['user_id'];
        $testId = createTest($questionsData, $createdBy);

        echo json_encode(['test_id' => $testId]);
    } else {
        echo json_encode(['error' => 'Invalid input data.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
