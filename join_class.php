<?php
// TEMPORARY test endpoint: simulates the LMS "join class" action.
// Later, the LMS group will insert into `enrollments` themselves
// and you can delete this file.
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: student.php');
    exit;
}

$userId    = (int) $_SESSION['user_id'];
$subjectId = (int) ($_POST['subject_id'] ?? 0);
$action    = $_POST['action'] ?? 'join';

if ($subjectId > 0) {
    if ($action === 'leave') {
        $stmt = $conn->prepare('DELETE FROM enrollments WHERE user_id = ? AND subject_id = ?');
    } else {
        // INSERT IGNORE + UNIQUE key = joining twice does nothing
        $stmt = $conn->prepare('INSERT IGNORE INTO enrollments (user_id, subject_id) VALUES (?, ?)');
    }
    $stmt->bind_param('ii', $userId, $subjectId);
    $stmt->execute();
}

header('Location: student.php');
exit;
