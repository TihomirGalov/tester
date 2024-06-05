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

        // Read the headers
        $headers = fgetcsv($handle, 1000, ",");

        // Initialize arrays to hold data for each table
        $questionsData = [];
        $questionsDetailsData = [];
        $answersData = [];

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $timestamp = date('Y-m-d H:i:s', strtotime($data[0]));
            $faculty_number = $data[1];
            $question_number = $data[2];
            $purpose = $data[3];
            $type = $data[4];
            $description = $data[5];
            $answers = array_slice($data, 6, 4);
            $correct_answer_index = intval($data[10]) - 1;
            $difficulty_level = $data[11];
            $feedback_correct = $data[12];
            $feedback_incorrect = $data[13];
            $remarks = $data[14];

            // Append to questions array
            $questionsData[] = [$description];

            // Append to question details array
            $questionsDetailsData[] = [
                $timestamp, $faculty_number, $question_number, $purpose, $type, $correct_answer_index + 1,
                $difficulty_level, $feedback_correct, $feedback_incorrect, $remarks
            ];

            // Append answers to answers array
            foreach ($answers as $index => $answer) {
                $is_correct = ($index === $correct_answer_index) ? 1 : 0;
                $answersData[] = [$answer, $is_correct];
            }
        }

        fclose($handle);

        // Write to temporary CSV files
        $questionsTempFile = tempnam(sys_get_temp_dir(), 'questions');
        $questionDetailsTempFile = tempnam(sys_get_temp_dir(), 'question_details');
        $answersTempFile = tempnam(sys_get_temp_dir(), 'answers');

        $questionsHandle = fopen($questionsTempFile, 'w');
        foreach ($questionsData as $row) {
            fputcsv($questionsHandle, $row);
        }
        fclose($questionsHandle);

        $questionDetailsHandle = fopen($questionDetailsTempFile, 'w');
        foreach ($questionsDetailsData as $row) {
            fputcsv($questionDetailsHandle, $row);
        }
        fclose($questionDetailsHandle);

        $answersHandle = fopen($answersTempFile, 'w');
        foreach ($answersData as $row) {
            fputcsv($answersHandle, $row);
        }
        fclose($answersHandle);

        $createdBy = $_SESSION['user_id'];

        // Create a new test
        $stmt = $conn->prepare("INSERT INTO tests (name, created_by) VALUES (?, ?)");
        $stmt->bind_param("si", $testName, $createdBy);
        $stmt->execute();
        $testId = $stmt->insert_id;
        $stmt->close();

        // Load data from temporary files into respective tables
        $conn->query("LOAD DATA INFILE '$questionsTempFile' INTO TABLE questions FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\n' (description) SET test_id = $testId");
        $conn->query("LOAD DATA INFILE '$questionDetailsTempFile' INTO TABLE question_details FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\n' (timestamp, faculty_number, question_number, purpose, type, correct_answer, difficulty_level, feedback_correct, feedback_incorrect, remarks)");
        $conn->query("LOAD DATA INFILE '$answersTempFile' INTO TABLE answers FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\n' (value, is_correct)");

        // Clean up temporary files
        unlink($questionsTempFile);
        unlink($questionDetailsTempFile);
        unlink($answersTempFile);

        echo json_encode(['test_id' => $testId]);
    } else {
        echo json_encode(['error' => 'File upload error.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
