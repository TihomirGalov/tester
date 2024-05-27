<?php
global $conn;
include 'db.php';
include 'utilities.php';

// Start the session
session_start();

//The csv file should be in the following format
//question,answer1,is_true,answer2,is_true,answer3,is_true,answer4,is_true

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] === UPLOAD_ERR_OK) {
        $csvFile = $_FILES['csvFile']['tmp_name'];
        $handle = fopen($csvFile, "r");

        //Read the headers
        $headers = fgetcsv($handle, 1000, ",");

        // Create a new test
        $createdBy = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO tests (created_by) VALUES (?)");
        $stmt->bind_param("i", $createdBy);
        $stmt->execute();
        $testId = $stmt->insert_id;
        $stmt->close();

        // Prepare statements for inserting questions and answers
        $stmtQuestion = $conn->prepare("INSERT INTO questions (description, test_id) VALUES (?, ?)");
        $stmtAnswer = $conn->prepare("INSERT INTO answers (question_id, value, is_correct) VALUES (?, ?, ?)");

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Insert the question
            $stmtQuestion->bind_param("si", $data[0], $testId);
            $stmtQuestion->execute();
            $questionId = $stmtQuestion->insert_id;

            // Insert the answers
            for ($i = 1; $i < count($data); $i += 2) {
                $answer = $data[$i];
                $is_correct = $data[$i + 1];
                $stmtAnswer->bind_param("isi", $questionId, $answer, $is_correct);
                $stmtAnswer->execute();
            }
        }

        fclose($handle);
        $stmtQuestion->close();
        $stmtAnswer->close();

        echo json_encode(['test_id' => $testId]);
    } else {
        echo json_encode(['error' => 'File upload error.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
