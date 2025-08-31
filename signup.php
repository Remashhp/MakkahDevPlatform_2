<?php

session_start();
include 'Connect.php'; // الاتصال بالقاعدة
$error = '';
$success = '';




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // استقبل البيانات من الفورم
    $name = $_POST['name'] ?? '';
    $national_id = $_POST['national_id'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $organization_id = $_POST['organization'] ?? '';
    $city_id = $_POST['city'] ?? '';
    $workplace_id = $_POST['workplace'] ?? '';
    $category_id = $_POST['category'] ?? '';

    // هاش كلمة المرور
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // أدخل البيانات
    $stmt = $conn->prepare("
        INSERT INTO users 
        (name, national_id, phone, gender, email, password, organization_id, city_id, workplace_id, category_id, phone_verified, email_verified)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0)
    ");

    $stmt->bind_param(
        'ssssssiiii',
        $name,
        $national_id,
        $phone,
        $gender,
        $email,
        $password,
        $organization_id,
        $city_id,
        $workplace_id,
        $category_id
    );
if ($stmt->execute()) {
    // خزن الـ ID في السيشن
    $new_user_id = $stmt->insert_id;
    $_SESSION['user_id'] = $new_user_id;

    // بوب اب عادي ثم ريداركت
    echo "<script>
        alert('تم إنشاء الحساب بنجاح!');
        window.location.href = 'home.php';
    </script>";
} else {
    echo "<script>
        alert('فشل إنشاء الحساب: " . addslashes($stmt->error) . "');
    </script>";
}



}
?>



<?php
$user_id = $_SESSION['user_id'] ?? 0;

$user = null;

if ($user_id) {
    $stmt = $conn->prepare("
        SELECT u.name, u.email, u.phone, w.name AS workplace_name
        FROM users u
        LEFT JOIN workplace w ON u.workplace_id = w.id
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows) {
        $user = $result->fetch_assoc();
    }
    $stmt->close();
}
?>




<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>إنشاء حساب جديد</title>
    <link rel="icon" type="image/png" href="images/logo.png" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
   body {
      font-family: 'Sakkal Majalla', 'Tahoma', sans-serif;
    }

    /* Custom colors and utilities */
    .bg-main {
      background-color: #e6f2f1;
    }

    .text-primary-custom {
      color: #3C4F4F;
    }

    .text-accent {
      color: #0DA9A6;
    }

    .bg-accent {
      background-color: #0DA9A6;
    }

    .bg-accent:hover {
      background-color: #0b8885;
    }

    .bg-gradient-footer {
      background: linear-gradient(to right, #3D7EB9, #43989A, #4BAF79);
    }
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 90px;
            background-color: #e6f2f1;
            z-index: 999;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
        }

        .logo-wrapper {
            display: flex;
            align-items: center;
        }

        .logo {
            height: 115px;
            width: auto;
        }

        .nav-wrapper {
            display: flex;
            align-items: center;
            gap: 40px;
        }

        .navbar {
            display: flex;
            gap: 30px;
            padding: 10px 15px;
            border-radius: 10px;
            justify-content: center;
            align-items: center;
        }

        .navbar a {
            text-decoration: none;
            color: #3c4f4f;
            font-weight: bold;
            font-size: 24px;
            transition: all 0.3s ease;
        }

        .navbar a:hover {
            color: #0da9a6;
            transform: scale(1.1);
        }

        .login-btn {
            background-color: #0da9a6;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            font-size: 24px;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            background-color: #0b8885;
            transform: scale(1.05);
        }

        .menu-toggle {
            display: none;
            font-size: 32px;
            background: none;
            border: none;
            cursor: pointer;
            color: #3c4f4f;
        }

        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 180px 20px 50px;
            width: 100%;
            background-color: white;
        }

        .outer-border {
            border-radius: 10px;
            padding: 30px;
            width: 680px;
            max-width: 720px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            margin: 0 auto;
            background: #fff;
            border: 1px solid #ddd;
        }

        .header-section {
            z-index: 1000;
            background-color: #fff;
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }

        .form-logo {
            width: 120px;
            height: auto;
            margin-bottom: 8px;
            display: block;
            margin: 0 auto 1.2rem;
        }

        .form-title {
            color: #0ba09d;
            font-weight: bold;
            font-size: 1.7rem;
            text-align: center;
            margin-bottom: 1.5rem;
            font-family: 'Sakkal Majalla', sans-serif;
        }

        .inner-border {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 1.2rem;
            background: #fafafa;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            font-size: 1.2rem;
            margin-bottom: 5px;
            color: #555;
        }

        .label-with-confirm {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .confirm-link {
            color: #007bff;
            text-decoration: underline;
            cursor: pointer;
            font-size: 0.9rem;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            margin-left: 10px;
        }

        .confirm-link.show {
            opacity: 1;
            visibility: visible;
        }

        .confirm-link:hover {
            color: #0056b3;
            text-decoration: none;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            font-size: 1.1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #fafafa;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        select.form-control {
            height: 40px;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23555'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: left 10px center;
            background-size: 15px;
            padding-right: 10px;
        }

        .phone-container {
            position: relative;
        }

        .phone-input-wrapper {
            display: flex;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #fafafa;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .country-code {
            background-color: #f0f0f0;
            padding: 10px 15px;
            border-left: 1px solid #ccc;
            font-size: 1.1rem;
            color: #555;
            display: flex;
            align-items: center;
            min-width: 60px;
            justify-content: center;
        }

        .phone-input {
            border: none;
            outline: none;
            padding: 10px 15px;
            font-size: 1.1rem;
            background-color: transparent;
            flex: 1;
            box-shadow: none;
        }

        .phone-input::placeholder {
            color: #aaa;
            font-size: 1rem;
        }

        .verification-section {
            margin-top: 10px;
            padding: 15px;
            background-color: #e8f5e8;
            border-radius: 6px;
            border: 1px solid #28a745;
            display: none;
        }

        .verification-section.show {
            display: block;
        }

        .verification-input {
            width: 100%;
            padding: 10px;
            font-size: 1.1rem;
            border: 1px solid #28a745;
            border-radius: 4px;
            margin-bottom: 10px;
            text-align: center;
            letter-spacing: 2px;
        }

        .verify-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-left: 5px;
        }

        .verify-btn:hover {
            background-color: #0056b3;
        }

        .resend-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .resend-btn:hover {
            background-color: #545b62;
        }

        .full-width {
            grid-column: span 2;
        }

        .form-footer {
            grid-column: span 2;
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
        }

        .submit-btn {
            background-color: #0ba09d;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 1.3rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            min-width: 200px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .submit-btn:hover {
            background-color: #0b8c8a;
            transform: translateY(-2px);
        }

        .submit-btn:disabled {
            background-color: #0ba09d;
            cursor: not-allowed;
            transform: none;
        }

        .success-message {
            color: #28a745;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        footer {
            width: 100%;
            text-align: center;
            padding: 20px;
            color: white;
            background: linear-gradient(to right, #3d7eb9, #43989a, #4baf79);
            margin-top: auto;
        }

        .footer-logo {
            width: 100px;
            margin-bottom: 10px;
        }

        .footer-menu {
            list-style: none;
            padding: 0;
            margin: 10px 0;
            display: flex;
            justify-content: center;
            gap: 77px;
        }

        .footer-menu li a {
            text-decoration: none;
            color: #ffffff;
            font-weight: bold;
        }

        .footer-text {
            margin-top: 15px;
            font-size: 14px;
            color: #ffffff;
        }

        @media (max-width: 768px) {
            .main-header {
                flex-wrap: wrap;
                padding: 15px 20px;
                width: 90%;
            }

            .logo {
                height: 80px;
            }

            .nav-wrapper {
                flex-direction: column;
                align-items: flex-start;
                width: 100%;
            }

            .navbar {
                flex-direction: column;
                align-items: flex-start;
                width: 100%;
                display: none;
                background-color: #e6f2f1;
                padding: 10px 0;
                margin-top: 10px;
            }

            .navbar a {
                font-size: 20px;
                padding: 10px 20px;
                width: 100%;
            }

            .login-btn {
                font-size: 20px;
                padding: 8px 20px;
                margin-right: 20px;
            }

            .menu-toggle {
                display: block;
            }

            .navbar.active {
                display: flex;
            }

            .navbar .login-btn {
                width: calc(100% - 40px);
                margin: 10px 20px;
                font-size: 20px;
                padding: 10px;
                text-align: center;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .full-width,
            .form-footer {
                grid-column: span 1;
            }

            .form-title {
                font-size: 1.5rem;
            }

            .outer-border {
                padding: 15px;
                margin: 0 10px;
            }

            .inner-border {
                padding: 15px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            }

            main {
                padding: 160px 10px 30px;
            }

            .footer-text {
                font-size: 10px;
            }

            .footer-menu {
                font-size: 15px;
                gap: 17px;
                flex-wrap: wrap;
            }

            .footer-logo {
                width: 80px;
            }

            .label-with-confirm {
                flex-direction: column;
                align-items: flex-start;
            }

            .confirm-link {
                margin-left: 0;
                margin-top: 2px;
            }
        }

        
    /* Login Modal */
    .modal-content {
      border-radius: 10px;
      border: 1px solid #e0e0e0;
    }

    .modal-inner {
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      background-color: rgba(133, 135, 135, 0);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }

    .form-control {
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }

    /* Custom button styles */
    .btn-custom {
      background-color: #0da9a6;
      border-color: #0da9a6;
      transition: all 0.3s ease;
    }

    .btn-custom:hover {
      background-color: #098e8c;
      border-color: #098e8c;
      transform: scale(1.05);
    }

    
    .profile-btn {
  background-color: #0DA9A6; /* أخضر */
  color: #fff; /* أيقونة بيضاء */
  border: none; /* بدون ستروك */
  padding: 10px 30px; /* عريض شوي */
  border-radius: 50px; /* بيضاوي */
  font-size: 20px;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: all 0.3s ease;
}

.profile-btn:hover {
  /*background-color:rgb(181, 232, 192);  أخضر أغمق عند الهوفر */
  transform: scale(1.05);
}

    /* Mobile specific adjustments */
    @media (max-width: 768px) {
      .hero-section {
        min-height: 450px;
      }

      .second-content {
        min-height: 200px;
      }

      .program-card img {
        height: 150px;
      }

      .hide-mobile {
        display: none !important;
      }
    }

    </style>
    
</head>

<body>
  <!-- Header -->
  <header class="bg-main fixed-top">
    <nav class="navbar navbar-expand-lg">
      <div class="container-fluid px-4">
        <!-- Logo -->
        <a class="navbar-brand" href="#">
          <img src="images/Logo_with_info.png" alt="شعار" height="96" class="d-inline-block align-top">
        </a>

        <!-- Mobile toggle button -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
          aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="text-primary-custom fs-2">☰</span>
        </button>

        <!-- Navigation menu -->
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item">
              <a class="nav-link text-primary-custom fw-bold fs-5 mx-2" href="home.php">الرئيسية</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-primary-custom fw-bold fs-5 mx-2" href="aboutUs.php">من نحن</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-primary-custom fw-bold fs-5 mx-2" href="education_achievements.php">الإنجازات</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-primary-custom fw-bold fs-5 mx-2" href="courses.php">البرامج التدريبية</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-primary-custom fw-bold fs-5 mx-2" href="training_record.php">سجلي التدريبي</a>
            </li>

            <li class="nav-item">
<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0): ?>
  <!-- عرض أيقونة البروفايل والدروب داون -->
  <div class="dropdown">
    <button class="profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
      <i class="fas fa-user"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-end profile-dropdown">
      <div class="dropdown-item">
        <strong>مرحبا بك!</strong>
      </div>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#accountModal">
        <i class="fas fa-user-circle"></i>
        حسابي
      </a>
      <a class="dropdown-item" href="logout.php">
        <i class="fas fa-sign-out-alt"></i>
        تسجيل الخروج
      </a>
    </div>
  </div>
<?php else: ?>
  <!-- عرض زر الدخول -->
  <button class="btn btn-custom text-white fw-bold fs-5 rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#loginModal">
    الدخول
  </button>
<?php endif; ?>
</li>

          </ul>
        </div>
      </div>
    </nav>
  </header>


    <script>
        function toggleMenu() {
            const nav = document.getElementById('navbar');
            nav.classList.toggle('active');
        }

        function openLoginModal() {
            alert('فتح نافذة الدخول (اختياري تنفيذه لاحقاً).');
        }
    </script>

    <main>
        <div class="outer-border">
            <div class="header-section">
                <img src="images/logo.png" alt="شعار النظام" class="form-logo" />
                <h1 class="form-title">إنشاء حساب جديد</h1>

                <?php if ($error): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php elseif ($success): ?>
                    <div class="success-message"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
            </div>

            <div class="inner-border">
            <form class="form-grid" id="signupForm" method="POST" action="">
    <div class="form-group">
        <label for="name">الاسم (الرباعي)</label>
        <input type="text" id="name" name="name" class="form-control" placeholder="ادخل اسمك بالكامل" required>
    </div>

    <div class="form-group">
        <label for="national-id">الهوية الوطنية</label>
        <input type="text" id="national-id" name="national_id" maxlength="10" class="form-control" placeholder="ادخل رقم الهوية" required>
    </div>

    <div class="form-group">
        <div class="label-with-confirm">
            <label for="phoneInput">رقم الجوال</label>
            <a href="#" class="confirm-link" id="confirmLink">تأكيد</a>
        </div>
        <div class="phone-container">
            <div class="phone-input-wrapper">
                <input type="text" id="phoneInput" name="phone" class="phone-input" placeholder="5xxxxxxxx" inputmode="numeric" maxlength="9" required>
                <div class="country-code">+966</div>
            </div>
            <!-- قسم التحقق -->
            <div class="verification-section" id="verificationSection">
                <label for="verificationCode">رمز التحقق:</label>
                <input type="text" id="verificationCode" name="verification_code" class="verification-input" placeholder="أدخل رمز التحقق" maxlength="6">
                <div>
                    <button type="button" class="verify-btn" id="verifyBtn">تحقق</button>
                    <button type="button" class="resend-btn" id="resendBtn">إعادة إرسال</button>
                </div>
                <div id="verificationMessage"></div>
            </div>
            <div id="phoneMessage"></div>
        </div>
    </div>

    <div class="form-group">
        <label for="gender">الجنس</label>
        <select id="gender" name="gender" class="form-control" required>
               <option value="">اختر الجنس</option>
               <option value="male">ذكر</option>
             <option value="female">أنثى</option>
         </select>
    </div>

    <div class="form-group">
        <label for="organization">جهة العمل</label>
        <select id="organization" name="organization" class="form-control" required>
            <option value="">اختر جهة العمل</option>
            <option value="1"> قسم</option>
            <option value="2"> مدرسة</option>
            <option value="3"> إدارة</option>

        </select>
    </div>

    <div class="form-group">
        <label for="city">المدينة/المنطقة</label>
        <select id="city" name="city" class="form-control" required>
            <option >اختر المدينة</option>
            <option value="1" >مكة المكرمة</option>
            <option value="2">القنفذة</option>
            <option value="3" >الليث</option>
        </select>
    </div>

<div class="form-group">
  <label for="workplace" id="workplaceLabel">اختر المدرسة/القسم</label>
  <select id="workplace" name="workplace" class="form-control" required>
    <option value="">اختر المدرسة/القسم</option>
  </select>
</div>


    <div class="form-group">
        <label for="category">التصنيف</label>
        <select id="category" name="category" class="form-control" required>
            <option value="">اختر التصنيف</option>
            <option value="1">الرسميين</option>
            <option value="2">بند الأجور</option>
            <option value="3">المستخدمين</option>
            <option value="4">مشرفين-تعليمي</option>
            <option value="5">مشرفين-اداري</option>
        </select>
    </div>

    <div class="form-group full-width">
        <label for="email">البريد الإلكتروني</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="example@mail.com" required>
    </div>

    <div class="form-group">
        <label for="password">كلمة المرور</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="********" minlength="8" required>
    </div>

    <div class="form-group">
        <label for="confirm-password">تأكيد كلمة المرور</label>
        <input type="password" id="confirm-password" name="confirm_password" class="form-control" placeholder="********" minlength="8" required>
    </div>

    <div class="form-footer">
      <button type="submit" class="submit-btn" id="submitBtn">إنشاء الحساب</button>
    </div>
</form>

            </div>
        </div>

        
  <!-- Login Modal -->
  <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <img src="images/logo.png" alt="شعار البوابة" class="mb-3" style="max-width: 120px;">
          <h4 class="text-accent fw-bold mb-4">تسجيل الدخول إلى البوابة التدريبية</h4>

          <div class="modal-inner p-4">
            <form method="POST" action="login.php">
              <div class="mb-3 text-end">
                <label for="nationalId" class="form-label fw-bold text-muted">الهوية الوطنية</label>
                <input type="text" class="form-control" id="nationalId" name="national_id" placeholder="أدخل رقم الهوية الوطنية" required>
              </div>
              <div class="mb-3 text-end">
                <label for="password" class="form-label fw-bold text-muted">كلمة المرور</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="أدخل كلمة المرور" required>
              </div>
              <button type="submit" class="btn btn-custom text-white px-4 py-2 mb-3">تسجيل الدخول</button>
            </form>


            <a href="#" class="d-block text-accent text-decoration-none fw-bold small mb-3">هل نسيت كلمة المرور؟</a>

            <div class="text-muted small">
              <span>ليس لديك حساب؟</span>
              <a href="signup.php" class="text-accent text-decoration-none fw-bold ms-1">إنشاء حساب</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  
<!-- Account Modal -->
<div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="accountModalLabel">حسابي</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <strong>الاسم:</strong> <?= htmlspecialchars($user['name'] ?? 'غير معروف') ?>
        </div>
        <div class="mb-3">
          <strong>البريد الإلكتروني:</strong> <?= htmlspecialchars($user['email'] ?? 'غير محدد') ?>
          <br><a href="update_email.php" class="text-primary">تحديث البريد الإلكتروني</a>
        </div>
        <div class="mb-3">
          <strong>رقم الهاتف:</strong> <?= htmlspecialchars($user['phone'] ?? 'غير محدد') ?>
          <br><a href="update_phone.php" class="text-primary">تحديث رقم الهاتف</a>
        </div>
        <div class="mb-3">
          <strong>جهة العمل:</strong> <?= htmlspecialchars($user['workplace_name'] ?? 'غير محدد') ?>
          <br><a href="update_workplace.php" class="text-primary">تحديث جهة العمل</a>
        </div>
        <div class="mb-3">
          <strong>كلمة المرور:</strong> **********
          <br><a href="update_password.php" class="text-primary">تحديث كلمة المرور</a>
        </div>
      </div>
    </div>
  </div>
</div>

    </main>

 <!-- Footer -->
  <footer class="bg-gradient-footer text-white py-4">
    <div class="container text-center">
      <img src="images/2030Vison_Logo.png" alt="شعار رؤية 2030" class="mb-3" style="width: 100px;">

      <ul class="list-inline mb-3">
        <li class="list-inline-item mx-3">
          <a href="#" class="text-white text-decoration-none fw-bold">تواصل معنا</a>
        </li>
        <li class="list-inline-item mx-3">
          <a href="#" class="text-white text-decoration-none fw-bold">سياسية الخصوصية</a>
        </li>
        <li class="list-inline-item mx-3">
          <a href="#" class="text-white text-decoration-none fw-bold">الشروط والاحكام</a>
        </li>
      </ul>

      <p class="small mb-0">
        &copy; 2025 جميع الحقوق محفوظة لإدارة تعليم مكة - لقسم الموارد البشرية - وحدة التخطيط والتطوير
      </p>
    </div>
  </footer>

<script>
document.getElementById('organization').addEventListener('change', function () {
  const orgValue = this.value;
  const label = document.getElementById('workplaceLabel');
  const workplaceSelect = document.getElementById('workplace');

  // تنظيف الخيارات
  workplaceSelect.innerHTML = '<option value="">اختر</option>';

  let labelText = '';
  let fetchType = '';

  if (orgValue === '1') {
    labelText = 'اختر القسم';
    fetchType = 'department';
  } else if (orgValue === '2') {
    labelText = 'اختر المدرسة';
    fetchType = 'school';
  } else if (orgValue === '3') {
    labelText = 'اختر الإدارة';
    fetchType = 'administration';
  } else {
    label.textContent = 'اختر المدرسة/القسم';
    return;
  }

  label.textContent = labelText;

  fetch(`get_workplaces.php?type=${fetchType}`)
    .then(res => res.json())
    .then(data => {
      if (!data.length) {
        workplaceSelect.innerHTML = '<option value="">لا توجد بيانات متاحة</option>';
      } else {
        data.forEach(item => {
          workplaceSelect.innerHTML += `<option value="${item.id}">${item.name}</option>`;
        });
      }
    })
    .catch(() => {
      workplaceSelect.innerHTML = '<option value="">خطأ في تحميل البيانات</option>';
    });
});
</script>

</body>

</html>
