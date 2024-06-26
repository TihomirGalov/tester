<?php
global $conn;

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
                LEFT JOIN finished_exams ON waiting_exams.test_id = finished_exams.test_id AND waiting_exams.user_id = finished_exams.user_id
                WHERE waiting_exams.user_id = ?
                AND finished_exams.test_id IS NULL';

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
$sql_solved = 'SELECT tests.name, finished_exams.id
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
    $htmlContent .= '<ul class="list-group">';
    foreach ($userTestsCreated as $testCreated) {
        $htmlContent .= '<li class="list-group-item">';
        $htmlContent .= '<span>' . $testCreated['name'] . '</span>';
        $htmlContent .= '<a href="test.html?&edit=1&test_id=' . $testCreated['id'] . '" class="badge ">Edit</a>';
        $htmlContent .= '</li>';
    }
    $htmlContent .= '</ul>';
} else {
    $htmlContent .= '<p>No tests created by you.</p>';
}
$htmlContent .= '</div>';

// Tests assigned to the user
$htmlContent .= '<div class="mt-3">';
$htmlContent .= '<h3>Tests Assigned to You</h3>';
if (!empty($userTestsAssigned)) {
    $htmlContent .= '<ul class="list-group">';
    foreach ($userTestsAssigned as $testAssigned) {
        $htmlContent .= '<li class="list-group-item">';
        $htmlContent .= '<span>' . $testAssigned['name'] . '</span>';
        $htmlContent .= '<a href="test.html?test_id=' . $testAssigned['id'] . '" class="badge">Solve</a>';
        $htmlContent .= '</li>';
    }
    $htmlContent .= '</ul>';
} else {
    $htmlContent .= '<p>No tests assigned to you.</p>';
}
$htmlContent .= '</div>';

// Tests solved by the user
$htmlContent .= '<div class="mt-3">';
$htmlContent .= '<h3>Tests Solved by You</h3>';
if (!empty($userTestsSolved)) {
    $htmlContent .= '<ul class="list-group">';
    foreach ($userTestsSolved as $testSolved) {
        $htmlContent .= '<li class="list-group-item">';
        $htmlContent .= '<span>' . $testSolved['name'] . '</span>';
        $htmlContent .= '<a href="results.html?test_id=' . $testSolved['id'] . '" class="badge">View Results</a>';
        $htmlContent .= '</li>';
    }
    $htmlContent .= '</ul>';
} else {
    $htmlContent .= '<p>No tests solved by you.</p>';
}
$htmlContent .= '</div>';

// Return the HTML content
echo $htmlContent;
?>
