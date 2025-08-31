<?php
session_start();
include 'Connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول للتسجيل في الدورة.']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$course_id = intval($_GET['id'] ?? 0);

if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'رقم الدورة غير صحيح.']);
    exit;
}

// تحقق هل سبق وسجل المستخدم في الدورة
$stmt = $conn->prepare("SELECT id FROM course_registrations WHERE user_id = ? AND course_id = ?");
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'أنت مسجل بالفعل في هذه الدورة.']);
    exit;
}
$stmt->close();

// إضافة طلب تسجيل جديد
$stmt = $conn->prepare("INSERT INTO course_registrations (user_id, course_id, status) VALUES (?, ?, 'قيد المراجعة')");
$stmt->bind_param("ii", $user_id, $course_id);

if ($stmt->execute()) {
    $conn->query("UPDATE course_info SET registered_count = registered_count + 1 WHERE id = $course_id");
    echo json_encode(['success' => true, 'message' => 'تم إرسال طلب التسجيل بنجاح.']);
} else {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء التسجيل. حاول لاحقاً.']);
}

$stmt->close();
$conn->close();
?>
