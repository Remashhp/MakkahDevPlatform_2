<?php
session_start();
include 'Connect.php';

$national_id = $_POST['national_id'] ?? '';
$password = $_POST['password'] ?? '';

if (!$national_id || !$password) {
    echo "<script>alert('الرجاء إدخال جميع البيانات'); window.history.back();</script>";
    exit;
}

// استعلام المستخدم مع الدور
$stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE national_id = ?");
$stmt->bind_param("s", $national_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('رقم الهوية غير موجود'); window.history.back();</script>";
    exit;
}

$user = $result->fetch_assoc();


// تحقق من كلمة المرور (بدون هاش)
if ($password === $user['password']) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['name'];
    $_SESSION['role'] = $user['role'];

    // التوجيه حسب الدور
   // بعد التحقق من كلمة المرور وتعيين الجلسة…
if ($user['role'] === 'متدرب') {
    echo "<script>
        alert('تم تسجيل الدخول بنجاح');
        // يستبدل الصفحة الحالية بدل إضافة سجل جديد
        window.location.replace('home.php');
    </script>";
    exit;

} elseif ($user['role'] === 'مدرب') {
    echo "<script>
        alert('تم تسجيل الدخول بنجاح');
        window.location.replace('trainer_dashboard.php');
    </script>";
    exit;

} elseif ($user['role'] === 'ادمن') {
    echo "<script>
        alert('تم تسجيل الدخول بنجاح');
        window.location.replace('HomeAdmin.php');
    </script>";
    exit;

} else {
    echo "<script>
        alert('دور المستخدم غير معروف');
        window.history.back();
    </script>";
    exit;
}

} 
else {
    echo "<script>alert('كلمة المرور غير صحيحة'); window.history.back();</script>";
    exit;
}

$stmt->close();
$conn->close();
?>