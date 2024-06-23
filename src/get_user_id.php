<?php
session_start();
if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] && isset($_SESSION['user_id'])) {
    echo json_encode(['user_id' => $_SESSION['user_id']]);
} else {
    echo json_encode(['user_id' => null]);
}
?>
