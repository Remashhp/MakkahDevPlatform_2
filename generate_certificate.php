<?php
session_start();
include 'Connect.php';

// تحميل مكتبة ArPHP لإصلاح الحروف العربية
require __DIR__ . '/arphp/src/Arabic.php';
use ArPHP\I18n\Arabic;

$arabic = new Arabic('Glyphs');

$user_id = $_SESSION['user_id'] ?? 0;
$registration_id = $_GET['registration_id'] ?? 0;
if (!$user_id || !$registration_id) {
    die('طلب غير صالح.');
}

// رفع حدود الذاكرة
ini_set('memory_limit', '1024M');

// جلب بيانات المستخدم والدورة + التواريخ
$stmt = $conn->prepare("
    SELECT u.name, c.course_title, c.start_date, c.end_date
    FROM users u
    JOIN course_registrations cr ON cr.user_id = u.id
    JOIN course_info c ON c.id = cr.course_id
    WHERE u.id = ? AND cr.id = ? AND cr.has_evaluation = 1
");

$stmt->bind_param("ii", $user_id, $registration_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result->num_rows) {
    die('بيانات غير موجودة');
}
$data = $result->fetch_assoc();

// تصليح النصوص العربية
$name         = $arabic->utf8Glyphs($data['name']);
$course_title = $arabic->utf8Glyphs( $data['course_title']);
$start_date   = date('d F Y', strtotime($data['start_date']));
$end_date     = date('d F Y', strtotime($data['end_date']));
$date_text    = $arabic->utf8Glyphs(" $start_date إلى $end_date");

// تحميل قالب الشهادة
$template_path = __DIR__ . '/uploads/Certificate_Template.png';
if (!file_exists($template_path)) {
    die("ملف القالب غير موجود.");
}


$image = imagecreatefrompng($template_path);

// إعداد اللون الأسود
$color = imagecolorallocate($image, 0, 0, 0);

// تحميل الخط
$font_path = __DIR__ . '/fonts/ArbFONTS-majalla.ttf';
if (!file_exists($font_path)) {
    die('خط غير موجود.');
}

// أبعاد الصورة لحساب التمركز
$image_width = imagesx($image);
$image_height = imagesy($image); // أضف هذا

// --- الإزاحات ---
$shift_left = 1900; // تحرك لليسار
$shift_left_2 = 2500; // تحرك لليسار
$base_y = $image_height * 0.45; // طلعت فوق شوي عن النص الأوسط
// --- طباعة اسم المستخدم ---
$name_font_size = 300;
$name_bbox      = imagettfbbox($name_font_size, 0, $font_path, $name);
$name_width     = abs($name_bbox[2] - $name_bbox[0]);
$name_x         = ($image_width - $name_width) / 2 - $shift_left;
$name_y         = $base_y;

imagettftext($image, $name_font_size, 0, $name_x, $name_y, $color, $font_path, $name);


// --- طباعة اسم البرنامج ---
$course_font_size = 300;
$course_bbox      = imagettfbbox($course_font_size, 0, $font_path, $course_title);
$course_width     = abs($course_bbox[2] - $course_bbox[0]);
$course_x         = ($image_width - $course_width) / 2 - $shift_left_2;
$course_y         = $name_y + 640;

imagettftext($image, $course_font_size, 0, $course_x, $course_y, $color, $font_path, $course_title);


// --- طباعة التاريخ ---
$date_font_size = 300;
$date_bbox      = imagettfbbox($date_font_size, 0, $font_path, $date_text);
$date_width     = abs($date_bbox[2] - $date_bbox[0]);
$date_x         = ($image_width - $date_width) / 2 - $shift_left_2;
$date_y         = $course_y + 630;

imagettftext($image, $date_font_size, 0, $date_x, $date_y, $color, $font_path, $date_text);
// حفظ الشهادة
// حفظ الشهادة
$output_dir = __DIR__ . '/certificates/generated/';
if (!is_dir($output_dir)) {
    mkdir($output_dir, 0777, true);
}
$output_path = $output_dir . "certificate_{$user_id}_{$registration_id}.png";
imagepng($image, $output_path);
imagedestroy($image);

// رجّع الصورة مباشرة للتحميل بدون عرض صفحة
header('Content-Type: image/png');
header("Content-Disposition: attachment; filename=certificate_{$user_id}_{$registration_id}.png");
readfile($output_path);
exit;

// عرض رابط التحميل
$relative_path = "certificates/generated/certificate_{$user_id}_{$registration_id}.png";
echo "<a href='$relative_path' download class='btn btn-custom'>تحميل الشهادة</a>";

// إنهاء الاتصال
$conn->close();
?>
