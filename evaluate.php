<?php
session_start();
include 'Connect.php';

$user_id = $_SESSION['user_id'] ?? 0;
$registration_id = $_GET['registration_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$user_id || !$registration_id) {
        echo "<script>alert('❌ بيانات المستخدم أو التسجيل غير موجودة.');</script>";
        exit;
    }

    // 1. جلب course_id من التسجيل
    $stmt = $conn->prepare("SELECT course_id FROM course_registrations WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $registration_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $reg = $res->fetch_assoc();
    $stmt->close();

    if (!$reg) {
        echo "<script>alert('❌ لم يتم العثور على بيانات التسجيل.');</script>";
        exit;
    }

    $course_id = $reg['course_id'];

    // 2. جلب اسم المستخدم
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    if (!$user) {
        echo "<script>alert('❌ لم يتم العثور على بيانات المستخدم.');</script>";
        exit;
    }

    // 3. جلب عنوان الدورة واسم المدرب
    $stmt = $conn->prepare("SELECT course_title, Trainer FROM courses WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $course = $res->fetch_assoc();
    $stmt->close();

    if (!$course) {
        echo "<script>alert('❌ لم يتم العثور على بيانات الدورة.');</script>";
        exit;
    }

    // 4. التحقق من الإجابات
    $answers = [];
    for ($i = 1; $i <= 15; $i++) {
        if (empty($_POST["answer$i"])) {
            echo "<script>alert('❌ يرجى تقييم السؤال رقم $i');</script>";
            exit;
        }
        $answers[$i] = $_POST["answer$i"];
    }

    $ans1 = trim($_POST['ans1'] ?? '');
    $ans2 = trim($_POST['ans2'] ?? '');

    if ($ans1 === '' || $ans2 === '') {
        echo "<script>alert('❌ يرجى ملء جميع الأسئلة المفتوحة.');</script>";
        exit;
    }

    // 5. جلب الأسئلة من قالب id = 1
    $stmt = $conn->prepare("SELECT * FROM survey WHERE id = 1 LIMIT 1");
    $stmt->execute();
    $template = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $questions = [];
    for ($i = 1; $i <= 15; $i++) {
        $questions[$i] = $template["q$i"] ?? '';
    }
    $q_open1 = $template["open1"] ?? '';
    $q_open2 = $template["open2"] ?? '';

    // 6. إدخال التقييم
    $stmt = $conn->prepare("
        INSERT INTO survey (
            user_id, user_name, course_info_id, course_title, trainer_name,
            q1, answer1, q2, answer2, q3, answer3, q4, answer4, q5, answer5,
            q6, answer6, q7, answer7, q8, answer8, q9, answer9, q10, answer10,
            q11, answer11, q12, answer12, q13, answer13, q14, answer14, q15, answer15,
            open1, ans1, open2, ans2
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?
        )
    ");

    $types = "isiss" . str_repeat("s", 34); // إجمالي 39 متغير
    $stmt->bind_param(
        $types,
        $user_id,
        $user['name'],
        $course_id,
        $course['course_title'],
        $course['Trainer'],
        $questions[1], $answers[1], $questions[2], $answers[2], $questions[3], $answers[3],
        $questions[4], $answers[4], $questions[5], $answers[5],
        $questions[6], $answers[6], $questions[7], $answers[7], $questions[8], $answers[8], $questions[9], $answers[9], $questions[10], $answers[10],
        $questions[11], $answers[11], $questions[12], $answers[12], $questions[13], $answers[13], $questions[14], $answers[14], $questions[15], $answers[15],
        $q_open1, $ans1, $q_open2, $ans2
    );

    if (!$stmt->execute()) {
        echo "<script>alert('❌ حدث خطأ أثناء حفظ البيانات: " . $stmt->error . "');</script>";
        exit;
    }
    $stmt->close();

    // 7. تحديث حالة التقييم
    $stmt = $conn->prepare("UPDATE course_registrations SET has_evaluation = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $registration_id, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: education_achievements.php");
    exit;
}
?>






<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>نموذج الاستبيان</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="icon" type="image/png" href="images/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body {
       font-family: 'Sakkal Majalla', 'Tahoma', sans-serif;

    }
    main {
           direction: rtl;
      background: #fff;
      padding: 36px 24px 60px 24px;
      max-width: 900px;
      margin: 0 auto;
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
    .title {
      color: #129ca0;
      margin-bottom: 0;
      margin-top: 100px;
      font-weight: bold;
    }
    .title-underline {
      border: 0;
      height: 4px;
      width: 220px;
      background: linear-gradient(90deg, #129ca0 60%, #fff 100%);
      margin-top: 0;
      margin-bottom: 40px;
    }
    .group-title {
      font-size: 1.1em;
      margin: 32px 0 12px 0;
    }
    .req {
      color: #d00;
      font-size: 0.85em;
      font-weight: normal;
    }
    table {
      width: 100%;
      background: #f9f9f9;
      border-radius: 8px;
      border-collapse: separate;
      margin-bottom: 8px;
      direction: rtl;
    }
    th, td {
      text-align: center;
      padding: 10px 6px;
      font-size: 1em;
    }
    th { color: #888; font-weight: 500; }
    .survey-table th.q-text,
    .survey-table td.q-text {
      text-align: right;
      font-weight: 400;
      padding-right: 14px;
      min-width: 180px;
      font-size: 1.07em;
    }
    .survey-table th.radio-header,
    .survey-table td.radio-cell {
      width: 65px;
    }
    .open-q { margin-top: 32px; }
    .open-input {
      width: 100%;
      min-height: 32px;
      font-size: 1em;
      background: #fafafa;
      border: 1px solid #eee;
      border-radius: 8px;
      margin-top: 8px;
      margin-bottom: 10px;
      padding: 10px;
    }
    .btns-row {
      display: flex;
      justify-content: center;
      margin: 56px 0 0 0;
    }
    .send-btn {
      min-width: 120px;
      padding: 10px 0;
      font-size: 1.1em;
      border-radius: 12px;
      border: none;
      background: #0cb5ac;
      color: #fff;
      cursor: pointer;
      transition: background 0.2s;
    }
    .send-btn:hover { background: #09978d; }
  </style>
</head>


<body>
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




<h2 class="title">نموذج الاستبيان</h2>
<hr class="title-underline" />

<div id="form-alert" class="alert d-none" role="alert"></div>

<form method="POST">
<?php
// الاتصال يجب أن يكون مفعّل قبل هذا (بـ include/connect)

// جلب الأسئلة من السطر id = 1
$stmt = $conn->prepare("SELECT * FROM survey WHERE id = 1 LIMIT 1");
$stmt->execute();
$questions_template = $stmt->get_result()->fetch_assoc();
$stmt->close();

$groups = [
  'تقييم المدربة' => [],
  'تقييم المحتوى التدريبي' => [],
  'البيئة التدريبية والخدمات المقدمة' => [],
];

for ($i = 1; $i <= 15; $i++) {
    $question = $questions_template["q$i"] ?? '';
    if ($i <= 5) {
        $groups['تقييم المدربة'][] = $question;
    } elseif ($i <= 10) {
        $groups['تقييم المحتوى التدريبي'][] = $question;
    } else {
        $groups['البيئة التدريبية والخدمات المقدمة'][] = $question;
    }
}

$open1 = $questions_template["open1"] ?? 'ماهي احتياجاتك التدريبية الأخرى؟';
$open2 = $questions_template["open2"] ?? 'اقتراحاتكم';

$q_num = 1;
$group_number = 1;

foreach ($groups as $group_title => $questions):
?>
  <div class="group-title">
    <b><?= $group_number++ ?>- <?= htmlspecialchars($group_title) ?> <span class="req">*</span></b>
  </div>
  <table class="survey-table">
    <thead>
      <tr>
        <th class="q-text"></th>
        <th class="radio-header">ممتاز</th>
        <th class="radio-header">جيد</th>
        <th class="radio-header">ضعيف</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($questions as $question): ?>
        <tr>
          <td class="q-text">
            <?= htmlspecialchars($question) ?>
            <input type="hidden" name="q<?= $q_num ?>" value="<?= htmlspecialchars($question) ?>">
          </td>
          <td class="radio-cell">
            <input type="radio" name="answer<?= $q_num ?>" value="ممتاز" required>
          </td>
          <td class="radio-cell">
            <input type="radio" name="answer<?= $q_num ?>" value="جيد">
          </td>
          <td class="radio-cell">
            <input type="radio" name="answer<?= $q_num ?>" value="ضعيف">
          </td>
        </tr>
        <?php $q_num++; ?>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endforeach; ?>

<!-- الأسئلة المفتوحة -->
<div class="open-q">
  <div class="group-title">
    <b><?= $q_num ?>- <?= htmlspecialchars($open1) ?> <span class="req">*</span></b>
  </div>
  <input type="hidden" name="open1" value="<?= htmlspecialchars($open1) ?>">
  <input type="text" name="ans1" class="open-input" required>
</div>

<div class="open-q">
  <div class="group-title">
    <b><?= $q_num + 1 ?>- <?= htmlspecialchars($open2) ?> <span class="req">*</span></b>
  </div>
  <input type="hidden" name="open2" value="<?= htmlspecialchars($open2) ?>">
  <input type="text" name="ans2" class="open-input" required>
</div>

<div class="btns-row">
  <button class="send-btn" type="submit" id="submitBtn">إرسال</button>
</div>
</form>



  </main>
    <!-- Footer -->




    
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


</body>
</html>
