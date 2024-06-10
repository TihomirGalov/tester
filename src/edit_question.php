<?php
global $conn;
include '../includes/db.php';

// Check if question ID is provided in the URL
if (!isset($_GET['id'])) {
    echo 'Question ID is required.';
    exit;
}

$questionId = $_GET['id'];

// Fetch question details from the database
$query = "SELECT * FROM questions WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $questionId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo 'Question not found.';
    exit;
}

$question = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Question</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Edit Question</h2>
    <form action="../src/update_question.php" method="post">
        <input type="hidden" name="id" value="<?php echo $question['id']; ?>">
        <div class="mb-3">
            <label for="description" class="form-label">Description:</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo $question['description']; ?></textarea>
        </div>
        <!-- Other fields of the question can be added here for editing -->
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
<!-- Bootstrap JS (Optional, only required if you have interactive Bootstrap components) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
