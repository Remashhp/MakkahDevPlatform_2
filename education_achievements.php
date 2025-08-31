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



$achievements = [];
if ($user_id) {
  $stmt = $conn->prepare("
        SELECT c.id, c.course_title, c.course_image, cr.status, cr.has_evaluation, cr.id AS registration_id
        FROM course_info c
        JOIN course_registrations cr ON c.id = cr.course_id
        WHERE cr.user_id = ? AND cr.status = 'مقبول'
    ");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $achievements[] = $row;
  }
  $stmt->close();
}

?>




<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <title>education achievements</title>
  <link rel="icon" type="image/png" href="images/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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



    /* ميديا كويري للشاشات الصغيرة */
    @media (max-width: 768px) {
      .main-header {

        flex-wrap: wrap;
        padding: 15px 20px;
        width: 90%;

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

      .profile_btn {
        width: 20px;
        font-size: 20px;
        padding: 8px 20px;

      }

      .menu-toggle {
        display: block;
      }

      .navbar.active {
        display: flex;
      }

      .navbar .profile_btn {
        width: calc(30% - 40px);
        margin: 10px 20px;
        font-size: 20px;
        padding: 10px;
        text-align: center;
      }

      .profile_btn {
        width: 20px;
        font-size: 20px;
        padding: 8px 20px;

      }

      .profile_wrapper {
        position: absolute;
        top: 510px;
        /* عدّل حسب مكان الزر */
        right: 60px;
        /* حسب التصميم */
        margin-top: 20px;
        margin-right: 109px;
        background-color: #ffffff;
        width: 340px;
        height: 175px;
        box-shadow: 0 1px 8px rgb(145, 145, 145);
        margin-bottom: 500PX;
        border-radius: 10px;

      }

      .User_FullName {
        font-size: 16px;


      }

      .Option_with_Icon {
        font-size: 16px;
      }



    }


    /*------------Footer style--------------*/

    footer {
      text-align: center;
      margin: 0px;
      padding: 20px;
      color: white;
      background: linear-gradient(to right, #3D7EB9, #43989A, #4BAF79);
      /* أخضر فاتح إلى أزرق */

    }

    .footer-logo {
      width: 100px;
      /* تحكمي في حجم الصورة */
      margin-bottom: 10px;
    }

    .footer-menu {
      list-style: none;
      /* يشيل النقط */
      padding: 0;
      margin: 10px 0;
      display: flex;
      justify-content: center;
      gap: 77px;
      /* المسافة بين العناصر */
    }

    .footer-menu li a {
      text-decoration: none;
      color: #ffffff;
      /* لون الروابط */
      font-weight: bold;
    }

    .footer-text {
      margin-top: 15px;
      font-size: 14px;
      color: #ffffff;
    }

    /*------------Courses page style--------------*/

    .container {
      max-width: 1200px;
      padding: 40px 20px;
      margin-top: 120px;
      /* مسافة من الهيدر */
    }

    .short_line {

      width: 280px;
      height: 5px;
      background-color: #0DA9A6;
      margin-right: 40px;
      border-top-left-radius: 20px;
      border-bottom-left-radius: 20px;

    }

    .long_line {

      width: 400px;
      height: 6px;
      background-color: #0D8AA9;
      margin-bottom: 40px;
      margin-right: 40px;
      border-top-left-radius: 20px;
      border-bottom-left-radius: 20px;
    }


    h2 {
      margin-top: 70px;
      margin-right: 20px;
      font-size: 32px;
      font-weight: bold;
      margin-bottom: 15px;
      text-align: right;
      color: #17A8A5;
    }

    .card {
      display: grid;
      grid-template-columns: auto 140px 70px auto 70px 1fr;
      align-items: center;
      gap: 16px;
      padding: 16px 32px;
      background-color: #fff;
      border: 1px solid #3B84B4;
      border-radius: 12px;
      width: 65%;
      margin: 0 auto 30px;
      direction: rtl;
    }

    .card-img {
      width: 100px;
      height: 60px;
      object-fit: cover;
      border-radius: 17px;
    }

    .content-wrapper {
      max-width: 140px;
      overflow: hidden;
    }

    .course-title {
      font-size: 20px;
      font-weight: bold;
      color: #3C4F4F;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      /* يعرض سطرين فقط */
      -webkit-box-orient: vertical;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .divider2 {
      width: 1px;
      height: 60px;
      background-color: #ccc;
      justify-self: center;
    }

    .date {
      font-size: 20px;
      font-weight: bold;
      color: #3C4F4F;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .link-wrapper {
      overflow: hidden;
      max-width: 250px;
    }

    .link {
      font-size: 20px;
      font-weight: bold;
      color: #159E9B;
      text-decoration: none;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: normal;
      /* يخليه يكسر السطر */
      word-break: break-word;
      /* يكسر الكلمة لو كانت طويلة */
    }

    .link:hover {
      text-decoration: underline;
    }







    /*ميديا للعنوان وخطوطه الفاصلة*/
    @media (max-width: 768px) {
      h2 {
        font-size: 32px;
        margin-right: 20px;
      }


      .short_line {
        width: 170px;
        margin-right: 20px;
      }

      .long_line {
        width: 260px;
        margin-right: 20px;
      }

      .card {
        flex-direction: row;
        align-items: center;
        grid-template-columns: auto 50px 1px auto 1px 1fr;
        padding: 16px;
        width: 80%;
        height: 60px;
      }

      .course-title {
        font-size: 12px;
        margin-left: 0px;

      }

      .date {
        font-size: 9px;
        margin-left: 0px;
        margin-right: 0px;
      }

      .link {
        font-size: 7px;
        font-weight: bold;
      }

      .card-img {
        width: 70px;
        height: 60px;
      }

      .container {

        padding-bottom: 0px;
        padding-right: 0px;
      }

      .footer-text {
        font-size: 10px;
        /* خفف حجم نص حقوق النشر */
      }

      .footer-menu {
        font-size: 15px;
        gap: 17px
      }

      .footer-logo {
        width: 80px;
        /* تصغير حجم الشعار */
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
      background-color: #0DA9A6;
      /* أخضر */
      color: #fff;
      /* أيقونة بيضاء */
      border: none;
      /* بدون ستروك */
      padding: 10px 30px;
      /* عريض شوي */
      border-radius: 50px;
      /* بيضاوي */
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
      background-color: #0DA9A6;
      /* أخضر */
      color: #fff;
      /* أيقونة بيضاء */
      border: none;
      /* بدون ستروك */
      padding: 10px 30px;
      /* عريض شوي */
      border-radius: 50px;
      /* بيضاوي */
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


  <script>
    function toggleProfile() {
      const profileDiv = document.getElementById('profileWrapper');
      if (profileDiv.style.display === 'none' || profileDiv.style.display === '') {
        profileDiv.style.display = 'block';
      } else {
        profileDiv.style.display = 'none';
      }
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




  <script>
    function toggleMenu() {
      const nav = document.getElementById('navbar');
      nav.classList.toggle('active');
    }

    function openLoginModal() {
      alert("فتح نافذة الدخول (اختياري تنفيذه لاحقاً).");
    }
  </script>

  <main>
    <div class="container">
      <h2>الانجازات التدريبية</h2>

      <div class="divider-lines_container">

        <div class="short_line">
        </div>
        <div class="long_line">
        </div>
      </div>
    </div>

    <?php if (empty($achievements)): ?>
      <p class="text-center mt-4">لا توجد إنجازات تعليمية حتى الآن.</p>
    <?php else: ?>
      <?php foreach ($achievements as $course): ?>
        <div class="card">
          <img src="<?= htmlspecialchars($course['course_image'] ?? 'images/default.png') ?>" alt="صورة الدورة" class="card-img">
          <div class="content-wrapper">
            <div class="course-title"><?= htmlspecialchars($course['course_title']) ?></div>
          </div>
          <div class="divider2"></div>
          <span class="date"><?= date('Y/m/d') // أو بدلها بتاريخ الدورة لو عندك 
                              ?></span>
          <div class="divider2"></div>
          <div class="link-wrapper">
            <?php if ($course['has_evaluation']): ?>
        <a href="generate_certificate.php?registration_id=<?= $course['registration_id'] ?>" class="link">تحميل الشهادة</a>


            <?php else: ?>
              <a href="evaluate.php?registration_id=<?= $course['registration_id'] ?>" class="link">ابدأ التقييم للحصول على الشهادة</a>
            <?php endif; ?>

          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>



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


  </main>



  
<!-- Account Modal -->
<div class="modal fade" id="accountModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
<div class="modal-header position-relative text-center">
  <h5 class="modal-title w-100">حسابي</h5>
  <button type="button" class="btn-close position-absolute start-0 top-0 m-3" data-bs-dismiss="modal" aria-label="إغلاق"></button>
</div>


      <div class="modal-body">
        <div class="mb-3">
          <div class="fw-bold">الاسم:</div>
          <div><?= htmlspecialchars($user['name'] ?? 'غير معروف') ?></div>
        </div>
        <div class="mb-3">
          <div class="fw-bold">البريد الإلكتروني:</div>
          <div><?= !empty($user['email']) ? htmlspecialchars($user['email']) : 'غير محدد' ?></div>
          <a href="#" class="text-primary small d-block mt-1" onclick="openUpdateEmailModal(event)">تحديث البريد الإلكتروني</a>
        </div>
        <div class="mb-3">
          <div class="fw-bold">رقم الهاتف:</div>
          <div><?= htmlspecialchars($user['phone'] ?? 'غير محدد') ?></div>
        <a href="#" class="text-primary small d-block mt-1" onclick="openPhoneUpdateModal(event)">تحديث رقم الهاتف</a>

        </div>
        <div class="mb-3">
          <div class="fw-bold">جهة العمل:</div>
          <div><?= htmlspecialchars($user['workplace_name'] ?? 'غير محدد') ?></div>
          <a href="#" class="text-primary small d-block mt-1" onclick="openUpdateWorkplaceModal(event)">تحديث جهة العمل</a>
        </div>
        <div class="mb-3">
          <div class="fw-bold">كلمة المرور:</div>
          <div>**********</div>
        <a href="#" class="text-primary small d-block mt-1" onclick="openUpdatePasswordModal(event)">تحديث كلمة المرور</a>

        </div>
      </div>
    </div>
  </div>
</div>



<!-- Update Email Modal -->
<div class="modal fade" id="updateEmailModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
    <div class="modal-header position-relative text-center">
  <h5 class="modal-title w-100">تحديث البريد الالكتروني</h5>
  <button type="button" class="btn-close position-absolute start-0 top-0 m-3" data-bs-dismiss="modal" aria-label="إغلاق"></button>
</div>

      <div class="modal-body">
        <form method="POST" action="update_email.php" onsubmit="return handleEmailUpdate(event)">
          <div class="mb-3">
            <label class="form-label">البريد الحالي</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">البريد الجديد</label>
            <input type="email" name="new_email" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-custom w-100">تحديث</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function openUpdateEmailModal(event) {
  event.preventDefault();
  const accountModal = bootstrap.Modal.getInstance(document.getElementById('accountModal'));
  accountModal.hide();
  setTimeout(() => {
    const updateModal = new bootstrap.Modal(document.getElementById('updateEmailModal'));
    updateModal.show();
  }, 300);
}

function handleEmailUpdate(event) {
  event.preventDefault();
  const form = event.target;
  const formData = new FormData(form);

  fetch('update_email.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(() => {
    const updateModal = bootstrap.Modal.getInstance(document.getElementById('updateEmailModal'));
    updateModal.hide();
    setTimeout(() => {
      location.reload();
    }, 300);
  })
  .catch(() => {
    alert('حدث خطأ أثناء التحديث');
  });

  return false;
}
</script>


<!-- Update Phone Modal -->
<div class="modal fade" id="updatePhoneModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
    <div class="modal-header position-relative text-center">
  <h5 class="modal-title w-100">تحديث رقم الهاتف</h5>
  <button type="button" class="btn-close position-absolute start-0 top-0 m-3" data-bs-dismiss="modal" aria-label="إغلاق"></button>
</div>

      <div class="modal-body">
        <form id="updatePhoneForm">
          <div class="mb-3">
            <label class="form-label">رقم الهاتف الجديد</label>
            <input type="text" name="new_phone" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-custom w-100">تحديث</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('updatePhoneForm').addEventListener('submit', function(event) {
  event.preventDefault();
  const form = event.target;
  const formData = new FormData(form);

  fetch('update_phone.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(response => {
    console.log(response); // عشان تشوف الرد
    if (response.includes('تم التحديث')) {
      const phoneModal = bootstrap.Modal.getInstance(document.getElementById('updatePhoneModal'));
      phoneModal.hide();
      location.reload();
    } else {
      alert('فشل التحديث: ' + response);
    }
  })
  .catch(err => {
    console.error(err);
    alert('حدث خطأ أثناء التحديث');
  });
});
</script>


<!-- Update Password Modal -->
<div class="modal fade" id="updatePasswordModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
<div class="modal-header position-relative text-center">
  <h5 class="modal-title w-100">تحديث كلمة المرور</h5>
 <a href="#" class="text-primary small d-block mt-1" onclick="openUpdateWorkplaceModal(event)">تحديث جهة العمل</a>

</div>

      <div class="modal-body">
        <form method="POST" action="update_password.php" onsubmit="return handlePasswordUpdate(event)">
          <div class="mb-3">
            <label class="form-label">كلمة المرور القديمة</label>
            <input type="password" name="old_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">كلمة المرور الجديدة</label>
            <input type="password" name="new_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">تأكيد كلمة المرور الجديدة</label>
            <input type="password" name="confirm_password" class="form-control" required>
          </div>
          <div class="text-center text-muted mb-2">هل نسيت كلمة المرور؟</div>
          <div class="text-center mb-3">
          <a href="#" onclick="openVerifyModal(); return false;" class="text-primary text-decoration-underline">إعادة تعيين كلمة المرور</a>

          </div>
          <button type="submit" class="btn btn-custom w-100">تحديث</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Verify Code Modal -->
<div class="modal fade" id="verifyCodeModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
         <div class="modal-header position-relative text-center">
  <h5 class="modal-title w-100">تأكيد رمز التحقق</h5>
  <button type="button" class="btn-close position-absolute start-0 top-0 m-3" data-bs-dismiss="modal" aria-label="إغلاق"></button>
</div>
      <div class="modal-body">
        <p class="small text-muted">ادخل رمز التحقق المرسل الى الايميل</p>
        <div class="d-flex justify-content-between mb-3">
          <input type="text" maxlength="1" class="form-control text-center" style="width: 40px;">
          <input type="text" maxlength="1" class="form-control text-center" style="width: 40px;">
          <input type="text" maxlength="1" class="form-control text-center" style="width: 40px;">
          <input type="text" maxlength="1" class="form-control text-center" style="width: 40px;">
          <input type="text" maxlength="1" class="form-control text-center" style="width: 40px;">
          <input type="text" maxlength="1" class="form-control text-center" style="width: 40px;">
        </div>
        <div class="text-center text-primary mb-3" style="cursor: pointer;" onclick="resendCode()">لم تستلم الرمز؟ إعادة الارسال</div>
        <button type="button" class="btn btn-custom w-100" onclick="verifyCode()">تحقق</button>
      </div>
    </div>
  </div>
</div>



<script>
function openPhoneUpdateModal(event) {
  event.preventDefault();
  const accountModal = bootstrap.Modal.getInstance(document.getElementById('accountModal'));
  accountModal.hide();
  setTimeout(() => {
    const phoneModal = new bootstrap.Modal(document.getElementById('updatePhoneModal'));
    phoneModal.show();
  }, 300);
}

function handlePhoneUpdate(event) {
  event.preventDefault();
  const form = event.target;
  const formData = new FormData(form);

  fetch('update_phone.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(() => {
    const phoneModal = bootstrap.Modal.getInstance(document.getElementById('updatePhoneModal'));
    phoneModal.hide();
    location.reload();
  })
  .catch(() => alert('حدث خطأ أثناء التحديث'));

  return false;
}

function openUpdatePasswordModal(event) {
  event.preventDefault();
  const accountModal = bootstrap.Modal.getInstance(document.getElementById('accountModal'));
  accountModal.hide();
  setTimeout(() => {
    const passModal = new bootstrap.Modal(document.getElementById('updatePasswordModal'));
    passModal.show();
  }, 300);
}


function handlePasswordUpdate(event) {
  event.preventDefault();
  const form = event.target;
  const formData = new FormData(form);

  fetch('update_password.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(responseText => {
    alert(responseText);  // يعرض رسالة من الـ PHP سواء نجاح أو خطأ
    if (responseText.includes('نجاح')) {
      const passModal = bootstrap.Modal.getInstance(document.getElementById('updatePasswordModal'));
      passModal.hide();
      location.reload();
    }
  })
  .catch(() => alert('حدث خطأ أثناء التحديث'));

  return false;
}

function openVerifyModal() {
  const passModal = bootstrap.Modal.getInstance(document.getElementById('updatePasswordModal'));
  passModal.hide();
  setTimeout(() => {
    const verifyModal = new bootstrap.Modal(document.getElementById('verifyCodeModal'));
    verifyModal.show();
  }, 300);
}

function resendCode() {
  alert('تم إرسال الرمز مجدداً');
}

function verifyCode() {
  alert('تم التحقق بنجاح');
  const verifyModal = bootstrap.Modal.getInstance(document.getElementById('verifyCodeModal'));
  verifyModal.hide();
  location.reload();
}
</script>


<!-- Modal لتحديث جهة العمل -->
<div class="modal fade" id="updateWorkplaceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="update_workplace.php">
        <div class="modal-header">
          <h5 class="modal-title">تحديث جهة العمل</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">

          <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
          <input type="hidden" id="currentType" value="<?= htmlspecialchars($workplace_type) ?>">

          <div class="mb-3">
            <label class="form-label">جهة العمل الجديدة</label>
            <select class="form-control" name="new_workplace_id" id="workplaceOptions" required>
              <option value="">جارٍ التحميل...</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">حفظ التغييرات</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
function openAccountModal(e) {
  e.preventDefault();
  const modal = new bootstrap.Modal(document.getElementById('accountModal'));
  modal.show();
}

function openUpdateModal(type) {
  const accountModal = bootstrap.Modal.getInstance(document.getElementById('accountModal'));
  accountModal.hide();
  setTimeout(() => {
    const targetId = {
      email: 'updateEmailModal',
      phone: 'updatePhoneModal',
      workplace: 'updateWorkplaceModal',
      password: 'updatePasswordModal'
    }[type];
    if (targetId) {
      const updateModal = new bootstrap.Modal(document.getElementById(targetId));
      updateModal.show();
    }
  }, 300);
}
</script>

<script>
function openUpdateWorkplaceModal(event) {
  event.preventDefault();
  const type = document.getElementById('currentType').value;
  const select = document.getElementById('workplaceOptions');

  select.innerHTML = '<option value="">جارٍ التحميل...</option>';

  fetch(`get_workplaces.php?type=${type}`)
    .then(res => res.json())
    .then(data => {
      if (!data.length) {
        select.innerHTML = '<option value="">لا توجد بيانات</option>';
      } else {
        select.innerHTML = '<option value="">اختر جهة العمل</option>';
        data.forEach(w => {
          const option = document.createElement('option');
          option.value = w.id;
          option.textContent = w.name;
          select.appendChild(option);
        });
      }

      new bootstrap.Modal(document.getElementById('updateWorkplaceModal')).show();
    })
    .catch(() => {
      select.innerHTML = '<option value="">حدث خطأ أثناء التحميل</option>';
    });
}
</script>



  <footer>

    <img src="images/2030Vison_Logo.png" alt="شعار رؤية 2030" class="footer-logo">
    <ul class="footer-menu">
      <li><a href="#">تواصل معنا</a></li>
      <li><a href="#">سياسية الخصوصية</a></li>
      <li><a href="#">الشروط والاحكام </a></li>


    </ul>
    <h3 class="footer-text"> &copy; 2025 جميع الحقوق محفوظة لإدارة تعليم مكة - لقسم الموارد البشرية - وحدة التخطيط والتطوير</h3>

  </footer>



</body>

</html>