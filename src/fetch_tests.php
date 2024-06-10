<?php
global $conn;

// Include your database connection script
include '../includes/db.php';

// Fetch user ID from session
$user_id = $_SESSION['user_id'];

// Fetch tests created by the logged-in user
$sql_created = 'SELECT tests.name, tests.id
                FROM tests
                WHERE tests.created_by = ?';

$stmt_created = $conn->prepare($sql_created);
$stmt_created->bind_param('i', $user_id);
$stmt_created->execute();
$result_created = $stmt_created->get_result();
$userTestsCreated = [];
if ($result_created->num_rows > 0) {
    while ($row_created = $result_created->fetch_assoc()) {
        $userTestsCreated[] = $row_created;
    }
}
$stmt_created->close();

// Fetch tests assigned to the logged-in user
$sql_assigned = 'SELECT tests.name, tests.id
                FROM waiting_exams
                INNER JOIN tests ON waiting_exams.test_id = tests.id
                WHERE waiting_exams.user_id = ?';

$stmt_assigned = $conn->prepare($sql_assigned);
$stmt_assigned->bind_param('i', $user_id);
$stmt_assigned->execute();
$result_assigned = $stmt_assigned->get_result();
$userTestsAssigned = [];
if ($result_assigned->num_rows > 0) {
    while ($row_assigned = $result_assigned->fetch_assoc()) {
        $userTestsAssigned[] = $row_assigned;
    }
}
$stmt_assigned->close();

// Fetch tests solved by the logged-in user
$sql_solved = 'SELECT tests.name, tests.id
                FROM finished_exams
                INNER JOIN tests ON finished_exams.test_id = tests.id
                WHERE finished_exams.user_id = ?';

$stmt_solved = $conn->prepare($sql_solved);
$stmt_solved->bind_param('i', $user_id);
$stmt_solved->execute();
$result_solved = $stmt_solved->get_result();
$userTestsSolved = [];
if ($result_solved->num_rows > 0) {
    while ($row_solved = $result_solved->fetch_assoc()) {
        $userTestsSolved[] = $row_solved;
    }
}
$stmt_solved->close();
$conn->close();
// Generate HTML content for the tests
$htmlContent = '';

// Tests created by the user
$htmlContent .= '<div class="mt-3">';
$htmlContent .= '<h3>Tests Created by You</h3>';
if (!empty($userTestsCreated)) {
    foreach ($userTestsCreated as $testCreated) {
        $htmlContent .= '<a href="test.html?test_id=' . $testCreated['id'] . '" class="btn btn-primary mb-2">Test ' . $testCreated['name'] . '</a>';
    }
} else {
    $htmlContent .= '<p>No tests created by you.</p>';
}
$htmlContent .= '</div>';

// Tests assigned to the user
$htmlContent .= '<div class="mt-3">';
$htmlContent .= '<h3>Tests Assigned to You</h3>';
if (!empty($userTestsAssigned)) {
    foreach ($userTestsAssigned as $testAssigned) {
        $htmlContent .= '<a href="test.html?test_id=' . $testAssigned['id'] . '" class="btn btn-primary mb-2">Test ' . $testAssigned['name'] . '</a>';
    }
} else {
    $htmlContent .= '<p>No tests assigned to you.</p>';
}
$htmlContent .= '</div>';

// Tests solved by the user
$htmlContent .= '<div class="mt-3">';
$htmlContent .= '<h3>Tests Solved by You</h3>';
if (!empty($userTestsSolved)) {
    foreach ($userTestsSolved as $testSolved) {
        // Load results.html     $_SESSION['score'] = $score;
        //    $_SESSION['finished_exam_id'] = $exam_id;
        $htmlContent .= '<a href="results.html?test_id=' . $testSolved['id'] . '" class="btn btn-primary mb-2">Test ' . $testSolved['name'] . '</a>';
    }
} else {
    $htmlContent .= '<p>No tests solved by you.</p>';
}
// Return the HTML content
echo $htmlContent;
?>
