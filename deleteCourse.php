<?php
// deleteCourse.php
header('Content-Type: application/json');

// استقبال JSON
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id'])) {
    echo json_encode(['success' => false]);
    exit;
}
$id = intval($data['id']);

// اتصال
include 'Connect.php';

// حذف
$stmt = $conn->prepare("DELETE FROM course_info WHERE id = ?");
$stmt->bind_param("i", $id);
$ok = $stmt->execute();
$stmt->close();
$conn->close();

// إرجاع النتيجة
echo json_encode(['success' => (bool)$ok]);
exit;
