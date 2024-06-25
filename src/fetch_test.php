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
        $creator = $_POST['creator']; // Retrieve creator from POST
        $testPurpose = $_POST['test_purpose']; // Retrieve test purpose from POST
        list($questionRangeMin, $questionRangeMax) = explode('-', $_POST['question_range']);
        $questionRangeMin = intval($questionRangeMin);
        $questionRangeMax = intval($questionRangeMax);

        $handle = fopen($csvFile, "r");

        // Read the headers
        $headers = fgetcsv($handle, 1000, ",");

        // Initialize arrays to hold data for each table
        $questionsData = [];
        $questionsDetailsData = [];
        $answersData = [];

        // Create a new test
        $createdBy = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO tests (name, created_by) VALUES (?, ?)");
        $stmt->bind_param("si", $testName, $createdBy);
        $stmt->execute();
        $testId = $stmt->insert_id;
        $stmt->close();

        // Insert questions, question details, and answers
        $questionStmt = $conn->prepare("INSERT INTO questions (test_id, description) VALUES (?, ?)");
        $questionDetailsStmt = $conn->prepare(
            "INSERT INTO question_details (question_id, timestamp, faculty_number, question_number, purpose, type, correct_answer, difficulty_level, feedback_correct, feedback_incorrect, remarks) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $answerStmt = $conn->prepare("INSERT INTO answers (value, question_id, is_correct) VALUES (?, ?, ?)");

        // Skip questions that are not due to criteria
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($creator && $data[1] !== $creator) {
                continue;
            }
            if ($testPurpose && $data[3] !== $testPurpose) {
                continue;
            }
            break;
        }

        $questions_counter = 1;
        // Skip the questions that are not in the range
        while ($questions_counter < $questionRangeMin) {
            $data = fgetcsv($handle, 1000, ",");
            $questions_counter++;
        }

        while ($questions_counter <= $questionRangeMax) {
            if ($creator && $data[1] !== $creator) {
                continue;
            }
            if ($testPurpose && $data[3] !== $testPurpose) {
                continue;
            }

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

            // Insert into questions table
            $questionStmt->bind_param("is", $testId, $description);
            $questionStmt->execute();
            $questionId = $questionStmt->insert_id;

            // Insert into question details table
            $answer_index = $correct_answer_index + 1;
            $questionDetailsStmt->bind_param(
                "issisisisss",
                $questionId, $timestamp, $faculty_number, $question_number, $purpose, $type,
                $answer_index, $difficulty_level, $feedback_correct, $feedback_incorrect, $remarks
            );
            $questionDetailsStmt->execute();

            // Insert into answers table
            foreach ($answers as $index => $answer) {
                $is_correct = ($index === $correct_answer_index) ? 1 : 0;
                $answerStmt->bind_param("sii", $answer, $questionId, $is_correct);
                $answerStmt->execute();
            }

            $questions_counter++;

            if (!($data = fgetcsv($handle, 1000, ","))) {
                break;
            }
        }

        // Close statements and file handle
        $questionStmt->close();
        $questionDetailsStmt->close();
        $answerStmt->close();
        fclose($handle);

        $users = json_decode($_POST['users']);
        assignTest($testId, $users);

        echo json_encode(['test_id' => $testId]);
    } else {
        echo json_encode(['error' => 'File upload error.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
