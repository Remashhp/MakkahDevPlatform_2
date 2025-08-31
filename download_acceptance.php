<?php
require 'connect.php'; // تأكد من ملف الاتصال

// تحميل مكتبة ArPHP لإصلاح الحروف العربية
require __DIR__ . '/arphp/src/Arabic.php';
use ArPHP\I18n\Arabic;

$arabic = new Arabic('Glyphs');

$user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
$course_id = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;

if (!$user_id || !$course_id) {
  die('البيانات ناقصة');
}

// رفع حدود الذاكرة
ini_set('memory_limit', '1024M');

// سحب بيانات المستخدم والدورة
$sql = "
  SELECT 
    u.name, u.national_id,
    c.course_title, c.start_date, c.course_duration,
    c.start_time, c.location_name, c.attendance_method
  FROM users u, course_info c
  WHERE u.id = $user_id AND c.id = $course_id
";

$result = $conn->query($sql);
if (!$result || $result->num_rows == 0) {
  die('بيانات غير موجودة');
}

$row = $result->fetch_assoc();

// جلب مسار قالب إشعار القبول من جدول attachments
$sqlTemplate = "SELECT acceptance_notification 
FROM attachments 
WHERE acceptance_notification IS NOT NULL 
ORDER BY id DESC 
LIMIT 1";

$resTemplate = $conn->query($sqlTemplate);

if (!$resTemplate || $resTemplate->num_rows == 0) {
  die('قالب إشعار القبول غير موجود');
}

$rowTemplate = $resTemplate->fetch_assoc();

// بناء المسار الكامل للصورة
$template = __DIR__ . '/' . $rowTemplate['acceptance_notification'];

// تأكد من وجود الملف قبل فتحه
if (!file_exists($template)) {
  die('ملف قالب إشعار القبول غير موجود في المسار: ' . $template);
}

// مسار الخط العربي
$font = __DIR__ . '/fonts/ArbFONTS-majalla.ttf'; // نفس الخط العربي حق الشهادة

// فتح الصورة
$image = imagecreatefrompng($template);
if (!$image) {
  die('فشل في تحميل صورة القالب');
}
// أبعاد الصورة لحساب منتصفها
$image_width = imagesx($image);
$image_height = imagesy($image);

// اللون الأسود
$black = imagecolorallocate($image, 0, 0, 0);

// حجم الخط المناسب (جرب 40 أو 50 بدل 300)
$font_size = 200;

// نقطة بداية رأسية (حسب حجم الصورة)
$start_y1 = $image_height / 2 - 2200; // نزّل النص شوي تحت المنتصف
$start_y = $image_height / 2 - 1300; // نزّل النص شوي تحت المنتصف

// مسافة عمودية بين الأسطر
$line_spacing = 550; // تباعد مناسب مع حجم الخط

// إصلاح النص العربي
$name = $arabic->utf8Glyphs($row['name']);
$national_id = $arabic->utf8Glyphs($row['national_id']);
$course_title = $arabic->utf8Glyphs($row['course_title']);

$start_date = $arabic->utf8Glyphs(date('Y-m-d', strtotime($row['start_date']))); // صيغة التاريخ مع المعالجة
$duration = $arabic->utf8Glyphs($row['course_duration']); // مدة الدورة مع المعالجة

$start_time = $arabic->utf8Glyphs($row['start_time']);
$location = $arabic->utf8Glyphs($row['attendance_method'] == 'عن بعد' ? 'Microsoft Teams' : $row['location_name']);

$x = 3800; // إزاحة أفقية ثابتة
$x2 =4700; // إزاحة أفقية ثابتة

// حساب عرض التاريخ
$date_box = imagettfbbox($font_size, 0, $font, $start_date);
$date_width = abs($date_box[2] - $date_box[0]);

$space_between = 50; // المسافة بين التاريخ والمدة

// طباعة النصوص
imagettftext($image, $font_size, 0, $x, $start_y1, $black, $font, $name);
imagettftext($image, $font_size, 0, $x, $start_y1 + $line_spacing * 1, $black, $font, $national_id);
imagettftext($image, $font_size, 0, $x, $start_y + $line_spacing * 2, $black, $font, $course_title);

// طباعة التاريخ يمين
imagettftext($image, $font_size, 0, $x2, $start_y + $line_spacing * 3, $black, $font, $start_date);

// طباعة المدة يسار التاريخ بمسافة ثابتة
imagettftext($image, $font_size, 0, $x - $date_width - $space_between, $start_y + $line_spacing * 3, $black, $font, $duration);

imagettftext($image, $font_size, 0, $x, $start_y + $line_spacing * 4, $black, $font, $start_time);
imagettftext($image, $font_size, 0, $x, $start_y + $line_spacing * 5, $black, $font, $location);

// تحميل الصورة للمستخدم
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="acceptance.png"');
imagepng($image);
imagedestroy($image);
exit;
?>
