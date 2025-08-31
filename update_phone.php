<?php
session_start();
include 'Connect.php';

if (!isset($_SESSION['user_id']) || empty($_POST['new_phone'])) {
    http_response_code(400);
    echo 'طلب غير صالح';
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$new_phone = trim($_POST['new_phone']);

// تحقق ما يكون نفس الرقم موجود لمستخدم آخر لو تبغى
$stmt = $conn->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
$stmt->bind_param("si", $new_phone, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo 'الرقم مستخدم بالفعل';
    exit;
}
$stmt->close();

// التحديث
$stmt = $conn->prepare("UPDATE users SET phone = ? WHERE id = ?");
$stmt->bind_param("si", $new_phone, $user_id);

if ($stmt->execute()) {
    echo 'تم التحديث بنجاح';
} else {
    http_response_code(500);
    echo 'فشل التحديث';
}

$stmt->close();
$conn->close();
?>
