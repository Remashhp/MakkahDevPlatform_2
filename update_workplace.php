<?php
include 'Connect.php';
session_start();

$user_id = $_POST['user_id'] ?? 0;
$new_workplace_id = $_POST['new_workplace_id'] ?? 0;

if ($user_id && $new_workplace_id) {
    $stmt = $conn->prepare("UPDATE users SET workplace_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_workplace_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

header('Location: home.php'); // أو أي صفحة رجوع
exit;
