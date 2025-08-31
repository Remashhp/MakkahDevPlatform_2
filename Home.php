You said:
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



<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <title>Home</title>
  <link rel="icon" type="image/png" href="images/logo.png">
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

    /* Hero section */
    .hero-section {
      background-image: url('images/FirstPic.png');
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
      min-height: 100vh;
    }

    /* Second content section */
    .second-content {
      margin-top: 25px;
      background-image: url('images/pic2background.png');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      min-height: 380px;
    }

    /* Program cards */
    .program-card {
      width: 80%;
      border-radius: 35px;
      border: 1px solid #848484;
      overflow: hidden;
      transition: transform 0.3s ease;
    }

    .program-card:hover {
      transform: translateY(-5px);
    }

    .program-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 35px;
    }

    .status-available {
      color: #1BCC4A;
    }

    /* Partners logos */
    .partner-logo {
      background: #f9f9f9;
      padding: 10px 20px;
      border-radius: 12px;
      height: 80px;
      object-fit: contain;
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

  <main>
    <!-- Hero Section -->
    <section class="hero-section d-flex align-items-center justify-content-center text-white text-center" style="margin-top: 98px; ">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="p-5 rounded-4">
              <h1 class="display-4 fw-bold mb-4">معاً نبني كوادر متميزة عبر برامج <br> تدريبية مصممة خصيصاً لك</h1>
              <?php if (!isset($_SESSION['user_id'])): ?>
                <a class="btn btn-custom text-white fs-4 px-4 py-2 rounded" href="signup.php">انضم الآن</a>
              <?php endif; ?>

            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Second Content Section -->
    <section class="second-content d-flex align-items-center text-white mb-5">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-8 order-2 order-lg-1">
            <div class="text-end pe-lg-5">
              <h2 class="h3 fw-bold mb-3">عن البوابة التدريبية للموارد البشرية</h2>
              <p class="fs-5 lh-lg">
                نعنى بتقديم برامج تدريبية متخصصة تواكب تطورات سوق العمل<br>
                وتلبي احتياجات الجهات و الأفراد على حد سواء و نحرص على رفع كفاءة <br>
                رأس المال البشري من خلال تقديم حلول تدريبية مبتكرة تُبنى على <br>
                أسس علمية ومعايير جودة عالية.
              </p>
            </div>
          </div>
          <div class="col-lg-4 order-1 order-lg-2">
            <img src="images/bic2.png" alt="صورة البوابة" class="img-fluid">
          </div>
        </div>
      </div>
    </section>

    <section class="py-5">
      <div class="container">
        <div class="row mb-4">
          <div class="col-12">
            <h2 class="h2 fw-bold text-dark text-end mb-3">البرامج الأحدث</h2>
            <a href="courses.php" class="text-decoration-none text-accent fw-bold fs-4 float-end">← كل البرامج</a>
          </div>
        </div>

        <div class="row g-1 justify-content-center">
          <?php
          // الاتصال بقاعدة البيانات

          // جلب أحدث 3 دورات
          $query = "SELECT * FROM course_info ORDER BY start_date DESC LIMIT 3";
          $result = $conn->query($query);

          if ($result && $result->num_rows > 0) {
            while ($course = $result->fetch_assoc()) {
              // تحديد لون الحالة
              $statusColor = $course['status'] === 'متاح للتسجيل' ? '#1BCC4A' : '#F00';
          ?>
              <div class="col-lg-4 col-md-6">
                <a href="Course_Details.php?id=<?= (int)$course['id'] ?>" class="text-decoration-none">
                  <div class="program-card h-100">
                    <img src="<?= htmlspecialchars($course['course_image']) ?>" alt="<?= htmlspecialchars($course['course_title']) ?>" class="img-fluid">
                    <div class="card-body p-3 text-end">
                      <p class="fw-bold mb-2" style="color: <?= $statusColor ?>;">
                        <i class="fa-solid fa-circle me-2" style="font-size: 10px; color: <?= $statusColor ?>;"></i>
                        <?= htmlspecialchars($course['status']) ?>
                      </p>
                      <h3 class="h5 fw-bold text-dark mb-3"><?= htmlspecialchars($course['course_title']) ?></h3>
                      <div class="d-flex justify-content-between text-muted">
                          <p class="mb-0 small">
                          <i class="fa-solid fa-calendar-days me-1"></i>
                          يبدأ من <?= date('j/n/Y', strtotime($course['start_date'])) ?>
                        </p>
                        <p class="mb-0 small">
                          <i class="fa-solid fa-clock me-1"></i>
                          المدة <?= htmlspecialchars($course['course_duration']) ?>
                        </p>
                      
                      </div>
                    </div>
                  </div>
                </a>
              </div>
          <?php
            }
          } else {
            echo '<p class="text-center">لا توجد دورات متاحة حالياً.</p>';
          }
          ?>
        </div>
      </div>
    </section>


    <!-- Partners Section -->
    <section class="py-5 mb-5">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center">
            <h2 class="h2 fw-bold text-dark mb-4">شركاء النجاح</h2>
            <div class="d-flex justify-content-center gap-4 flex-wrap">
              <img src="images/uqu.png" alt="جامعة أم القرى" class="partner-logo">
              <img src="images/tvtc.png" alt="المؤسسة العامة للتدريب التقني والمهني" class="partner-logo">
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

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
          alert(responseText); // يعرض رسالة من الـ PHP سواء نجاح أو خطأ
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
        } [type];
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
    function handleEmailUpdate(event) {
      event.preventDefault();
      const form = event.target;
      const formData = new FormData(form);

      fetch('update_email.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.text())
        .then(response => {
          // أغلق بوب أب التحديث
          const updateModal = bootstrap.Modal.getInstance(document.getElementById('updateEmailModal'));
          updateModal.hide();

          // أعد فتح بوب أب الحساب
          const accountModal = new bootstrap.Modal(document.getElementById('accountModal'));
          accountModal.show();

          // ممكن تطبع رسالة نجاح هنا
          alert('تم تحديث البريد بنجاح');
          // أو حدث الصفحة لو تبغى تشوف التغيير على طول
          // location.reload();
        })
        .catch(err => {
          console.error('خطأ في التحديث', err);
          alert('حدث خطأ أثناء التحديث');
        });

      return false;
    }
  </script>
  <script>
    function openUpdateEmailModal(e) {
      e.preventDefault();

      // اقفل مودال الحساب
      const accountModalInstance = bootstrap.Modal.getInstance(document.getElementById('accountModal'));
      accountModalInstance.hide();

      // افتح مودال التحديث بعد شوي عشان يعطي وقت يقفل الأول
      setTimeout(() => {
        const updateEmailModalInstance = new bootstrap.Modal(document.getElementById('updateEmailModal'));
        updateEmailModalInstance.show();
      }, 300); // 300 ملي ثانية وقت كافي
    }
  </script>



</body>

</html>