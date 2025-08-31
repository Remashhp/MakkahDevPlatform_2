<?php
session_start();
include 'Connect.php';

if (!isset($_SESSION['user_id']) || empty($_POST['old_password']) || empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
    http_response_code(400);
    echo 'طلب غير صالح';
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$old_password = trim($_POST['old_password']);
$new_password = trim($_POST['new_password']);
$confirm_password = trim($_POST['confirm_password']);

if ($new_password !== $confirm_password) {
    echo 'كلمة المرور الجديدة وتأكيدها غير متطابقين';
    exit;
}

// تحقق من كلمة المرور القديمة بدون تشفير
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($current_password);

if ($stmt->fetch()) {
    if ($old_password !== $current_password) {
        echo 'كلمة المرور القديمة غير صحيحة';
        exit;
    }
} else {
    echo 'المستخدم غير موجود';
    exit;
}
$stmt->close();

// تحديث كلمة المرور
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $new_password, $user_id);

if ($stmt->execute()) {
    echo 'تم التحديث بنجاح';
} else {
    http_response_code(500);
    echo 'فشل التحديث';
}

$stmt->close();
$conn->close();
?>
