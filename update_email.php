<?php
session_start();
include 'Connect.php';

if (!isset($_SESSION['user_id']) || empty($_POST['new_email'])) {
    http_response_code(400);
    echo 'طلب غير صالح';
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$new_email = trim($_POST['new_email']);

// تحقق بسيط من البريد (تقدر تعزز التحقق لو تبغى)
if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo 'بريد إلكتروني غير صالح';
    exit;
}

// تحقق ما يكون مستخدم من قبل
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->bind_param("si", $new_email, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo 'البريد مستخدم بالفعل';
    exit;
}
$stmt->close();

// التحديث
$stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
$stmt->bind_param("si", $new_email, $user_id);

if ($stmt->execute()) {
    echo 'تم التحديث بنجاح';
} else {
    http_response_code(500);
    echo 'فشل التحديث';
}

$stmt->close();
$conn->close();
?>
