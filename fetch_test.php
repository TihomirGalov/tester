<?php
include 'db.php';


//The csv file should be in the following format
//question,answer1,is_true,answer2,is_true,answer3,is_true,answer4,is_true

function createTest($createdBy) {
    global $conn;

    $stmtTest = $conn->prepare("INSERT INTO tests (created_by) VALUES (?)");
    $stmtTest->bind_param("i", $createdBy);
    $stmtTest->execute();
    $test_id = $stmtTest->insert_id;
    $stmtTest->close();

    return $test_id;
}

function fetchTest($csvFile, $createdBy) {
    global $conn;

    $test_id = createTest($createdBy);

    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        //Read the headers
        $headers = fgetcsv($handle, 1000, ",");

        $stmtQuestion = $conn->prepare("INSERT INTO questions (description, test_id) VALUES (?, ?)");
        $stmtAnswer = $conn->prepare("INSERT INTO answers (question_id, value, is_correct) VALUES (?, ?, ?)");

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $stmtQuestion->bind_param("si", $data[0], $test_id);
            $stmtQuestion->execute();
            $question_id = $stmtQuestion->insert_id;

            for ($i = 1; $i < count($data); $i += 2) {
                $answer = $data[$i];
                $is_correct = $data[$i + 1];
                $stmtAnswer->bind_param("isi", $question_id, $answer, $is_correct);
                $stmtAnswer->execute();
            }
        }

        $stmtQuestion->close();
        $stmtAnswer->close();
        fclose($handle);

        echo json_encode(['test_id' => $test_id]);
    } else {
        echo json_encode(['error' => 'Unable to open CSV file.']);
    }
}

session_start();
$createdBy = $_SESSION['user_id'];
fetchTest('questions.csv', $createdBy);
?>
