<?php
ob_start();

include '../includes/db.php';
include '../includes/utilities.php';

// Start the session
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['questions']) && isset($data['answers']) && isset($data['correct_answers'])) {
        $testName = $data['test_name'];
        $questions = $data['questions'];
        $question_ids = $data['question_ids'];
        $answers = $data['answers'];
        $answer_ids = $data['answer_ids'];
        $correctAnswers = $data['correct_answers'];
        $users = $data['users'];
        $testId = $data['test_id'];

        $questionsData = [];

        for ($i = 0; $i < count($questions); $i++) {
            $question = $questions[$i];
            $questionAnswers = [];

            for ($j = 0; $j < 4; $j++) {
                $is_correct = ($j == $correctAnswers[$i]) ? 1 : 0;
                $questionAnswers[] = ['answer' => $answers[$i][$j], 'is_correct' => $is_correct, 'answer_id' => $answer_ids[$i][$j]];
            }

            $questionsData[] = [
                'question' => $question,
                'question_id' => $question_ids[$i],
                'answers' => $questionAnswers,
                'question_purposes' => $data['question_purposes'],
                'question_types' => $data['question_types'],
                'difficulty_levels' => $data['difficulty_levels'],
                'feedbacks_correct' => $data['feedbacks_correct'],
                'feedbacks_incorrect' => $data['feedbacks_incorrect'],
                'remarks' => $data['remarks']
            ];
        }

        $userId = $_SESSION['user_id'];
        updateTestQuestions($testId, $testName, $questionsData, $userId);

        header("Location: ../public/index.html");
        ob_end_flush();
    } else {
        echo json_encode(['error' => 'Invalid input data.']);
    }

} else {
    echo json_encode(['error' => 'Invalid request method.']);
}

?>