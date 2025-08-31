<?php
// connect.php
// --------------
// بيانات الاتصال بـ MySQL:
$host     = 'localhost';
$user     = 'root';      // غيّر إذا أنشأت مستخدم غير root
$password = 'Remasrr1';          // كلمة مرور المستخدم، اتركها فارغة إن لم تضبط واحدة
$dbName   = 'ministry_education_ksa';

$conn = new mysqli($host, $user, $password, $dbName);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
