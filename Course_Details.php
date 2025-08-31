<?php
session_start();
include 'Connect.php';
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


<?php

$user_id = $_SESSION['user_id'] ?? 0;
$user = null;

// جلب بيانات المستخدم لو مسجل دخول
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

// جلب بيانات الدورة
$course_id = intval($_GET['id'] ?? 0);
if (!$course_id) {
    die("رقم الدورة غير صحيح");
}
$stmt = $conn->prepare("SELECT * FROM course_info WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result->num_rows) {
    die("الدورة غير موجودة");
}
$course = $result->fetch_assoc();
$stmt->close();
$is_registered = false;
if ($user_id) {
    $stmt = $conn->prepare("SELECT id FROM course_registrations WHERE user_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $is_registered = true;
    }
    $stmt->close();
}

?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Course Details</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
  body {
     margin: 0;
    font-family: 'Sakkal Majalla', 'Tahoma', sans-serif;

           
  }
  

  /* Background colors */
  .bg-main {
    background-color: #e6f2f1;
  }

  /* Text colors */
  .text-primary-custom {
    color: #3C4F4F !important;
  }

  /* Navigation hover effects */
  .nav-link:hover {
    color: #0DA9A6 !important;
    transform: scale(1.1);
    transition: all 0.3s ease;
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

  .nav-centered-adjusted {
  margin-right: 30px;
}



  /* Modal styles */
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

  /* Main container with margins */
  .main-container {
    margin-left: 40px;
    margin-right: 40px;
    margin-top: 1000px; /* Adjusted for header height */
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

    

    #course_poster {
        background: linear-gradient(to right, #3D7EB9, #43989A, #4BAF79);
        color: white;

      padding: 2rem 0;

    }

    .content-wrapper {

      display: flex;
      align-items: center;
      justify-content: right;
      gap: 530px;
      padding-right: 80px;
    }

    #course_poster_name {
      font-size: 2rem;
      font-weight: bold;
      margin: 0;
    }

    #Poster_image {
      max-width: 300px;
      height: auto;
      border-radius: 20px;
    }

    .container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 1rem;
    }

    .right-side_dt {
      flex: 2;
      padding: 1rem;
    }

    .left-side {
      flex: 1;
      padding: 1rem;
    }

    .section-title {
      font-size: 1.5rem;
      font-weight: bold;
      margin-bottom: 1rem;
      color: #333;
    }

    .info_course {
      line-height: 1.6;
      margin-bottom: 1rem;
    }

    .info_course ul {
      padding-right: 1.5rem;
    }

    .info_course li {
      margin-bottom: 0.5rem;
    }

    .horizontal-line {
      border: none;
      height: 1px;
      background-color: #ddd;
      margin: 2rem 0;
    }

    .left-side h4 {
      font-size: 1.2rem;
      font-weight: bold;
      margin-bottom: 1rem;
      color: #333;
    }

    .info-box {
      background-color: #f8f9fa;
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 1rem;
      margin-bottom: 1.5rem;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .divider {
      width: 1px;
      height: 20px;
      background-color: #ddd;
      margin: 0 1rem;
    }

    .register-btn {
      background-color: #0DA9A6;
      color: white;
      border: none;
      padding: 1rem 2rem;
      border-radius: 20px;
      font-size: 1.1rem;
      width: 100%;
      cursor: pointer;
    }

    .register-btn:hover {
      background-color:rgb(5, 80, 79);
    }

  /* Footer styles */
  footer {
    background: linear-gradient(to right, #3D7EB9, #43989A, #4BAF79);
    color: white;
  }

  .footer-logo {
    width: 100px;
  }

  .footer-menu {
    list-style: none;
    padding: 0;
    margin: 10px 0;
  }

  .footer-menu li a {
    text-decoration: none;
    color: #ffffff;
    font-weight: bold;
  }

  .footer-text {
    font-size: 14px;
    color: #ffffff;
  }

    /* Responsive styles */
    @media (max-width: 768px) {
      .content-wrapper {
        flex-direction: column;
      }
      
      .navbar {
        flex-direction: column;
        gap: 1rem;
      }
      
      .navbar a, .navbar button {
        display: block;
        margin-bottom: 0.5rem;
      }
      
      #Poster_image {
        max-width: 100%;
        border-radius: 20px;
      }
      
      .footer-menu {
        flex-direction: column;
        gap: 0.5rem;
      }
    }
  </style>

  
<script>
function registerCourse(courseId) {
  const btn = document.getElementById('registerBtn');
  const btnText = document.getElementById('registerBtnText');

  // لا تغيّر التنسيق، فقط غيّر النص
  btnText.innerText = '...جاري التسجيل';
  btn.disabled = true;

  // Simulate AJAX request (غيره بالكود الحقيقي حقك)
  setTimeout(() => {
    btnText.innerText = 'مسجل';
  }, 2000);
}
</script>
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
  <div class="dropdown-menu dropdown-menu-end profile-dropdown text-end" dir="rtl">
    <div class="dropdown-item">
     <strong><?= htmlspecialchars($user['name'] ?? 'اسم المستخدم') ?></strong>

    </div>
    <div class="dropdown-divider"></div>
    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#accountModal">
      <i class="fas fa-user-circle me-2"></i>
      حسابي
    </a>
    <a class="dropdown-item text-danger" href="logout.php">
  <i class="fas fa-sign-out-alt me-2 text-danger"></i>
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



<!-- Course Poster -->
<main style="margin-top: 150px;">
  <div id="course_poster">
    <div class="overlay-text2">
      <div class="content-wrapper">
        <p id="course_poster_name">
          <strong><?= htmlspecialchars($course['course_title']) ?></strong>
        </p>
        <img id="Poster_image" src="<?= htmlspecialchars($course['course_image']) ?>" class="img-fluid">
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <!-- التفاصيل -->
      <div class="col-lg-8">
        <div class="right-side_dt">
          <h3 class="section-title">الوصف</h3>
          <p class="info_course"><?= nl2br(htmlspecialchars($course['course_description'])) ?></p>

          <hr class="horizontal-line">

          <h3 class="section-title">الأهداف</h3>
         <p class="info_course">
  <?= nl2br(htmlspecialchars(str_replace(["\\r\\n", "\\n", "\\r"], "\n", $course['course_objectives']))) ?>
</p>


          <hr class="horizontal-line">

          <h3 class="section-title">المتطلبات</h3>
<p class="info_course">
  <?= nl2br(htmlspecialchars(str_replace(["\\r\\n", "\\n", "\\r"], "\n", $course['course_requirements']))) ?>
</p>

        </div>
      </div>

      <!-- معلومات اضافية -->
      <div class="col-lg-4">
        <div class="left-side">
          <h4>تاريخ الدورة</h4>
          <div class="info-box">
            <div class="info-row">
              <span><?= htmlspecialchars($course['start_date']) ?></span>
              <div class="divider"></div>
              <span><?= htmlspecialchars($course['end_date']) ?></span>
            </div>
          </div>

          <h4>الوقت</h4>
          <div class="info-box">
            <div class="info-row">
              <span><?= htmlspecialchars($course['start_time']) ?></span>
              <div class="divider"></div>
              <span><?= htmlspecialchars($course['end_time']) ?></span>
            </div>
          </div>

          <h4>الآلية</h4>
          <div class="info-box">
            <div class="info-row">
              <span><?= htmlspecialchars($course['attendance_method']) ?></span>
              <div class="divider"></div>
              <span><strong>المدة:</strong> <?= htmlspecialchars($course['course_duration']) ?></span>
            </div>
          </div>

          <button class="register-btn" id="registerBtn" 
            <?= $is_registered ? 'disabled' : '' ?>
            onclick="<?= $is_registered ? '' : 'registerCourse(' . $course_id . ')' ?>">
            <span id="registerBtnText"><?= $is_registered ? 'مسجل' : 'التسجيل' ?></span>
          </button>
        </div>
      </div>
    </div>
  </div>
</main>

  <!-- Account Modal -->


  <!-- Footer -->
  <footer class="text-center py-4 mt-5">
    <div class="container">
      <img src="images/2030Vison_Logo.png" alt="شعار رؤية 2030" class="footer-logo mb-3">
      <ul class="footer-menu d-flex justify-content-center gap-4 mb-3">
        <li><a href="#" class="text-decoration-none">تواصل معنا</a></li>
        <li><a href="#" class="text-decoration-none">سياسة الخصوصية</a></li>
        <li><a href="#" class="text-decoration-none">الشروط والاحكام</a></li>
      </ul>
      <p class="footer-text mb-0">
        &copy; 2025 جميع الحقوق محفوظة لإدارة تعليم مكة - لقسم الموارد البشرية - وحدة التخطيط والتطوير
      </p>
    </div>
  </footer>


  <script>
function registerCourse(courseId) {
    fetch('register_course.php?id=' + courseId)
        .then(res => res.json())
        .then(data => {
            alert(data.message);  // تنبيه بسيط - تقدر تحطه SweetAlert أو Toast لو تبغى أحلى
            if (data.success) {
                const btn = document.getElementById('registerBtn');
                btn.textContent = 'مسجل';
                btn.disabled = true;
                btn.classList.remove('register-btn');
                btn.classList.add('btn-success');
                // رجع للواجهة بعد ثواني
                setTimeout(() => window.location.href = 'courses.php', 2000);
            }
        })
        .catch(() => {
            alert('فشل الاتصال بالخادم.');
        });
}
</script>

  
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>



</body>
</html>