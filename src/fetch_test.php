<?php
global $conn;
include '../includes/db.php';
include '../includes/utilities.php';

// Start the session
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] === UPLOAD_ERR_OK) {
        $csvFile = $_FILES['csvFile']['tmp_name'];
        $testName = $_POST['test_name'];
        $handle = fopen($csvFile, "r");

        //Read the headers
        $headers = fgetcsv($handle, 1000, ",");

        $questionsData = [];
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $questionData = ['question' => $data[0], 'answers' => []];

            for ($i = 1; $i < count($data); $i += 2) {
                $answerData = ['answer' => $data[$i], 'is_correct' => $data[$i + 1]];
                $questionData['answers'][] = $answerData;
            }

            $questionsData[] = $questionData;
        }

        fclose($handle);

        $createdBy = $_SESSION['user_id'];
        $testId = createTest($testName, $questionsData, $createdBy);
        assignTest($testId, [$_SESSION['user_id']]);

        echo json_encode(['test_id' => $testId]);
    } else {
        echo json_encode(['error' => 'File upload error.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>