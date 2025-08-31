<?php
// ربط الملف بقاعدة البيانات
include 'Connect.php'; // ملف يحتوي على الاتصال بقاعدة البيانات (مثل $conn)

// تحديد نوع المحتوى الراجع = JSON (عشان الجافاسكربت يستقبله بشكل صحيح)
header('Content-Type: application/json');

// أخذ قيمة النوع (type) من الرابط، مثلاً ?type=school
$type = $_GET['type'] ?? ''; // لو ما فيه قيمة، يحط فاضي

// التحقق من أن النوع المرسل مسموح به (لحماية الكود من القيم العشوائية)
if (!in_array($type, ['department', 'school', 'administration'])) {
    echo json_encode([]); // يرجّع مصفوفة فاضية لو النوع غير مدعوم
    exit; // يوقف التنفيذ
}

// تجهيز استعلام SQL لجلب الأماكن (مدارس، أقسام، إدارات) بناءً على النوع المطلوب
$stmt = $conn->prepare("SELECT id, name FROM workplace WHERE is_active = 1 AND type = ? ORDER BY name");

// ربط النوع كـ parameter في الاستعلام (لحماية من SQL Injection)
$stmt->bind_param('s', $type); // s = string

// تنفيذ الاستعلام
$stmt->execute();

// الحصول على النتائج
$result = $stmt->get_result();

// تحويل النتائج إلى مصفوفة
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row; // كل صف (id, name) يضاف للمصفوفة
}

// طباعة النتيجة بشكل JSON (يرجعها للجافاسكربت)
echo json_encode($data);

// إغلاق الاستعلام
$stmt->close();
