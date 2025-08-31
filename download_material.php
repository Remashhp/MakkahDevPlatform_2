
<?php
include 'Connect.php';

if (!isset($_GET['id'])) {
  die('رقم الدورة غير موجود');
}

$course_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT training_material FROM course_info WHERE id = ?");

$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  $file_path = $row['training_material'];
}



include 'Connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit('طلب غير صالح.');
}

$course_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT training_material FROM course_info WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $file_path = $row['training_material'];

    if (!file_exists($file_path)) {
        die('الملف غير موجود');
    }

    // تحميل الملف
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit;

} else {
    die('لم يتم العثور على الدورة');
}

$course_id = intval($_GET['id']);
$query = "SELECT training_material FROM course_info WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row || empty($row['training_material'])) {
    exit('الحقيبة غير موجودة.');
}

$file = $row['training_material'];
$file_path = __DIR__ . '/materials/' . basename($file); // تأكد من مكان تخزينك للملفات

if (!file_exists($file_path)) {
    exit('الملف غير موجود.');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
