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


$searchTerm = $_GET['search'] ?? '';    // قيمة البحث من الرابط أو الفورم، لو ما فيه خليها فاضية
$statusFilter = $_GET['status'] ?? 'all';  // حالة الفلترة، ممكن تكون 'all' أو 'completed' أو 'upcoming' أو 'current'
$searchTerm = "%{$searchTerm}%";  // عشان تستخدمها في LIKE

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <title>training record</title>
  <link rel="icon" type="image/png" href="images/logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap RTL CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Bootstrap JS -->
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

    .nav-centered-adjusted {
      margin-right: 30px;
    }


    /* Main container with margins */
    .main-container {
      margin-left: 40px;
      margin-right: 40px;
    }


    .search-input {
      position: relative;
    }

    .search-input .form-control {
      padding-right: 45px;
      border-radius: 8px;
      box-shadow: var(--card-shadow);
    }

    .search-input .fas {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #888;
      z-index: 10;
    }

    .filter-btn {
      border: 1px solid #ccc;
      background-color: white;
      color: #717878;
      border-radius: 8px;
      box-shadow: var(--card-shadow);
      transition: all 0.3s ease;
      font-size: 16px;
      padding: 10px 20px;
      margin: 0 5px;
      margin-bottom: 25px;
    }

    .filter-btn.active {
      background-color: #E7F4F3;
      border-color: var(--primary-color);
      color: var(--primary-color);
    }

    /* Cards */
    .program-card {
      border: 1px solid #848484;
      border-radius: 35px;
      overflow: hidden;
      margin-bottom: 35px;
      transition: transform 0.3s ease;
      height: 100%;
      margin-left: 0px;
    }

    .program-card:hover {
      transform: translateY(-5px);
    }

    .program-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-bottom-left-radius: 35px;
      border-bottom-right-radius: 35px;
    }

    .card-content {
      padding: 9px;
    }

    .status {
      color: var(--success-color);
      font-weight: bold;
      margin-bottom: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .program-title {
      font-size: 23px;
      font-weight: bold;
      color: #222;
      margin: 10px 0 20px 0;
    }

    .program-info {
      display: flex;
      gap: 20px;
      color: #6E6E6E;
      font-size: 17px;
    }

    .program-info i {
      color: #a0a0a0;
      margin-left: 5px;
    }

    .options-btn {
      background: none;
      border: none;
      color: #333;
      font-size: 20px;
      cursor: pointer;
      padding: 5px;
      border-radius: 50%;
      transition: background-color 0.3s ease;
    }

    .options-btn:hover {
      background-color: #f0f0f0;
    }

    /* Courses page styles */
    .short_line {
      width: 280px;
      height: 5px;
      background-color: #0DA9A6;
      border-top-right-radius: 20px;
      border-bottom-right-radius: 20px;
      margin: 0;
    }

    .long_line {
      width: 400px;
      height: 6px;
      background-color: #0D8AA9;
      border-top-right-radius: 20px;
      border-bottom-right-radius: 20px;
      margin: 0;
      margin-bottom: 40px;
    }

    .page-title {
      margin-top: 100px;
      font-size: 35px;
      font-weight: bold;
      color: #17A8A5;
    }

    /* Course Modal */
    .course-modal .modal-body {
      padding: 30px;
    }

    .modal-option {
      display: flex;
      align-items: center;
      padding: 15px 20px;
      margin-bottom: 10px;
      border-radius: 10px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      border: 1px solid #e0e0e0;
    }

    .modal-option:hover {
      background-color: #E7F4F3;
      border-color: var(--primary-color);
    }

    .modal-option i {
      font-size: 24px;
      margin-left: 15px;
      color: var(--primary-color);
      width: 30px;
      text-align: center;
    }

    .modal-option span {
      font-size: 18px;
      font-weight: 500;
    }


    /* Footer styles */
    footer {
      background: linear-gradient(to right, #3D7EB9, #43989A, #4BAF79);
      color: white;
      margin: 0;
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

    /* Responsive */
    @media (max-width: 1200px) {
      .main-container {
        margin-left: 30px;
        margin-right: 30px;
      }

      .program-info {
        gap: 20px;
      }
    }

    @media (max-width: 992px) {
      .main-container {
        margin-left: 20px;
        margin-right: 20px;
      }

      .program-info {
        gap: 15px;
        font-size: 15px;
      }
    }

    @media (max-width: 768px) {
      body {
        padding-top: 80px;
      }

      .main-container {
        margin-left: 15px;
        margin-right: 15px;
      }

      .logo {
        height: 60px;
      }

      .page-title {
        font-size: 32px;
      }

      .short-line {
        width: 170px;
      }

      .long-line {
        width: 260px;
      }

      .program-card img {
        height: 150px;
      }

      .program-title {
        font-size: 18px;
      }

      .program-info {
        font-size: 14px;
        gap: 10px;
        flex-direction: column;
      }

      .footer-menu {
        flex-direction: column;
        gap: 15px;
      }

      .footer-text {
        font-size: 12px;
      }

    }

    @media (max-width: 576px) {
      .main-container {
        margin-left: 10px;
        margin-right: 10px;
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
          <ul class="navbar-nav mx-auto align-items-center nav-centered-adjusted">
            <li class="nav-item">
              <a class="nav-link text-primary-custom fw-bold fs-5 mx-2" href="home.php">الرئيسية</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-primary-custom fw-bold fs-5 mx-2" href="aboutUs.php">من نحن</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-primary-custom fw-bold fs-5 mx-2" href="education_achievements.php">الإنجازات التعليمية</a>
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
                    <a class="dropdown-item" href="#" style="direction: rtl; text-align: right;" data-bs-toggle="modal" data-bs-target="#accountModal ">
                      <i class="fas fa-user-circle me-2"></i>
                      حسابي
                    </a>

                    <a class="dropdown-item text-danger" href="logout.php" style="direction: rtl; text-align: right;">
                      <i class="fas fa-sign-out-alt ms-2 text-danger"></i> تسجيل الخروج
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

  <main style="margin-top: 120px;">
    <div class="main-container">
      <!-- Page Title -->
      <div class="row">
        <div class="col-12">
          <h2 class="page-title text-start mb-4 fs-1">سجلي التدريبي</h2>
          <div class="d-flex flex-column align-items-start line-container">
            <div class="short_line"></div>
            <div class="long_line"></div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <form method="GET" id="filterForm">
        <div class="row filters-section align-items-center gx-1">
          <div class="col-lg-4 col-md-6 mb-3 pe-2">
            <div class="search-input" style="max-width: 320px;">
              <input type="text" name="search" id="searchInput" class="form-control" placeholder="البحث" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
              <i class="fas fa-search"></i>
            </div>
          </div>
          <div class="col-lg-8 col-md-6 mb-3 ps-2">
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn filter-btn <?= ($statusFilter === 'all') ? 'active' : '' ?>" data-status="all">الكل</button>
              <button type="button" class="btn filter-btn <?= ($statusFilter === 'completed') ? 'active' : '' ?>" data-status="completed">المكتملة</button>
              <button type="button" class="btn filter-btn <?= ($statusFilter === 'upcoming') ? 'active' : '' ?>" data-status="upcoming">القادمة</button>
              <button type="button" class="btn filter-btn <?= ($statusFilter === 'current') ? 'active' : '' ?>" data-status="current">الحالية</button>
            </div>
          </div>
          <input type="hidden" name="status" id="statusInput" value="<?= htmlspecialchars($statusFilter) ?>">
        </div>
      </form>

      <?php
      $user_id = $_SESSION['user_id'] ?? 0;

      $searchTerm = $_GET['search'] ?? '';
      $statusFilter = $_GET['status'] ?? 'all';
      $searchTerm = "%{$searchTerm}%";

      $query = "
SELECT 
  ci.id, 
  ci.course_title, 
  ci.start_date, 
  ci.end_date, 
  ci.course_duration, 
  ci.attendance_method, 
  ci.course_image,
  cr.status 
FROM course_registrations cr
JOIN course_info ci ON cr.course_id = ci.id
WHERE cr.user_id = ?
  AND cr.status IN ('مقبول', 'قيد المراجعة', 'مرفوض')
  AND ci.course_title LIKE ?
";

      $params = [$user_id, $searchTerm];
      $types = "is";

      $today = date('Y-m-d');

      if ($statusFilter === 'completed') {
        $query .= " AND ci.end_date < ?";
        $types .= "s";
        $params[] = $today;
      } elseif ($statusFilter === 'upcoming') {
        $query .= " AND ci.start_date > ?";
        $types .= "s";
        $params[] = $today;
      } elseif ($statusFilter === 'current') {
        $query .= " AND ci.start_date <= ? AND ci.end_date >= ?";
        $types .= "ss";
        $params[] = $today;
        $params[] = $today;
      }

      $stmt = $conn->prepare($query);
      $stmt->bind_param($types, ...$params);
      $stmt->execute();
      $result = $stmt->get_result();


      function getStatusColor($status)
      {
        switch ($status) {
          case 'مقبول':
            return '#1BCC4A';  // أخضر
          case 'قيد المراجعة':
            return '#FFA500';  // برتقالي
          case 'مرفوض':
            return '#FF0000';  // أحمر
          default:
            return '#888'; // رمادي
        }
      }

      ?>
      <script>
        document.querySelectorAll('.filter-btn').forEach(button => {
          button.addEventListener('click', () => {
            // حذف active من الكل
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            // إضافة active للزر اللي ضغط عليه
            button.classList.add('active');

            // تحديث القيمة في الحقل المخفي
            document.getElementById('statusInput').value = button.getAttribute('data-status');

            // إعادة إرسال الفورم عشان يظهر النتائج الجديدة
            document.getElementById('filterForm').submit();
          });
        });

        document.getElementById('searchInput').addEventListener('keydown', e => {
          if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('filterForm').submit();
          }
        });
      </script>


      <div class="row" id="coursesContainer">
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="program-card"
              data-course-type="<?= htmlspecialchars($row['attendance_method']) ?>"
              data-status="<?= htmlspecialchars($row['status']) ?>"
              data-start="<?= date('Y-m-d', strtotime($row['start_date'])) ?>"
              data-end="<?= date('Y-m-d', strtotime($row['end_date'])) ?>">
              <img src="<?= htmlspecialchars($row['course_image'] ?: 'images/default.png') ?>" alt="<?= htmlspecialchars($row['course_title']) ?>">
              <div class="card-content">
                <div class="status">
                  <div>
                    <i class="fa-solid fa-circle" style="color: <?= getStatusColor($row['status']) ?>; font-size: 10px;"></i>
                    <?= htmlspecialchars($row['status']) ?>
                  </div>
                  <?php if ($row['status'] === 'مقبول'): ?>
                    <button class="options-btn" data-bs-toggle="modal" data-bs-target="#courseModal"
                      onclick="showCourseOptions('<?= htmlspecialchars($row['course_title']) ?>', '<?= htmlspecialchars($row['attendance_method']) ?>', <?= $row['id'] ?>)">
                      <i class="fas fa-ellipsis-h"></i>
                    </button>

                  <?php endif; ?>
                </div>
                <h3 class="program-title"><?= htmlspecialchars($row['course_title']) ?></h3>
                <div class="program-info">
                  <div><i class="fa-solid fa-calendar-days"></i> يبدأ من <?= date('d/m/Y', strtotime($row['start_date'])) ?></div>
                  <div><i class="fa-solid fa-clock"></i> المدة <?= htmlspecialchars($row['course_duration']) ?></div>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>

      <!-- Course Options Modal -->
      <div class="modal fade" id="courseModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content course-modal">
            <div class="modal-header">
              <h5 class="modal-title" id="courseModalTitle">خيارات الدورة</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-option" onclick="downloadAcceptance(<?= $user_id ?>, currentCourseId)">
              <i class="fas fa-check-circle"></i>
              <span>إشعار القبول</span>
            </div>

            <script>
              function downloadAcceptance(userId, courseId) {
                window.location.href = `download_acceptance.php?user_id=${userId}&course_id=${courseId}`;
              }
            </script>


            <div class="modal-option" onclick="downloadTrainingKit(currentCourseId)">
              <i class="fas fa-briefcase"></i>
              <span>الحقيبة التدريبية</span>
            </div>

            <div class="modal-option" onclick="takePreTest()">
              <i class="fas fa-file-text"></i>
              <span>الاختبار القبلي</span>
            </div>

            <div class="modal-option" onclick="takePostTest()">
               <i class="fas fa-file-text"></i>
              <span>الاختبار البعدي</span>
            </div>
            <div class="modal-option" onclick="openLocation(currentCourseId)">
              <i class="fas fa-map-marker-alt"></i>
              <span>الموقع</span>
            </div>


          </div>
        </div>
      </div>
    </div>


<!-- Location Modal -->
<div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title w-100 text-center" id="locationModalLabel">موقع الدورة</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <div id="locationIcon" class="mb-3 fs-1 text-primary">
         
        </div>
        <h6 id="locationName">اسم المكان</h6>
        <!-- غيرت هنا -->
        <a href="#" target="_blank" id="locationAddress" class="d-block mb-2 text-decoration-none"></a>
        <a href="#" target="_blank" id="locationLink" class="btn btn-outline-primary">فتح الرابط</a>
      </div>
    </div>
  </div>
</div>



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
    document.querySelectorAll('.filter-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const selectedStatus = this.getAttribute('data-status');
        const today = new Date();

        document.querySelectorAll('.program-card').forEach(card => {
          const startDate = new Date(card.getAttribute('data-start'));
          const endDate = new Date(card.getAttribute('data-end'));
          const status = card.getAttribute('data-status');

          let show = false;

          if (selectedStatus === 'all') {
            show = true; // ممكن تضبط حسب حاجتك
          } else if (selectedStatus === 'upcoming') {
            show = startDate > today && status === 'مقبول';
          } else if (selectedStatus === 'current') {
            show = startDate <= today && endDate >= today && status === 'مقبول';
          } else if (selectedStatus === 'completed') {
            show = endDate < today && status === 'مقبول';
          }

          card.closest('.col-lg-3, .col-md-4, .col-sm-6').style.display = show ? '' : 'none';
        });
      });
    });

    // Search functionality
    document.querySelector('.search-input input').addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const cards = document.querySelectorAll('.program-card');

      cards.forEach(card => {
        const title = card.querySelector('.program-title').textContent.toLowerCase();
        // اضبط هنا العنصر الحاوي
        const cardContainer = card.closest('.col-lg-3, .col-md-4, .col-sm-6');

        if (title.includes(searchTerm)) {
          cardContainer.style.display = '';
        } else {
          cardContainer.style.display = 'none';
        }
      });
    });


    // Global variables to store current course info
    let currentCourseTitle = '';
    let currentCourseType = '';
    let currentCourseId = null;

    // Show course options modal
    function showCourseOptions(courseTitle, courseType, courseId) {
      currentCourseTitle = courseTitle;
      currentCourseType = courseType;
      currentCourseId = courseId;

      document.getElementById('courseModalTitle').textContent = courseTitle;

      const locationIcon = document.getElementById('locationIcon');
      const locationText = document.getElementById('locationText');

      if (courseType === 'online') {
        locationIcon.className = 'fas fa-link';
        locationText.textContent = 'رابط الدورة';
      } else {
        locationIcon.className = 'fas fa-map-marker-alt';
        locationText.textContent = 'موقع الدورة';
      }
    }
    // Course option functions
    function showAcceptanceNotification() {
      alert(`عرض إشعار القبول لدورة: ${currentCourseTitle}`);
      bootstrap.Modal.getInstance(document.getElementById('courseModal')).hide();
    }

    function downloadTrainingKit(courseId) {
      if (!courseId) return;
      window.location.href = `download_material.php?id=${courseId}`;
    }

    function takePreTest() {
      alert(`بدء الاختبار القبلي لدورة: ${currentCourseTitle}`);
      bootstrap.Modal.getInstance(document.getElementById('courseModal')).hide();
    }

    function takePostTest() {
      alert(`بدء الاختبار البعدي لدورة: ${currentCourseTitle}`);
      bootstrap.Modal.getInstance(document.getElementById('courseModal')).hide();
    }

function openLocation(courseId) {
  fetch(`get_course_location.php?id=${courseId}`)
    .then(response => response.json())
    .then(json => {
      if (json.status !== 'success') {
        alert('لم يتم العثور على بيانات الدورة.');
        return;
      }
      const data = json.data;

      const iconDiv = document.getElementById('locationIcon');
      const locationName = document.getElementById('locationName');
      const locationAddress = document.getElementById('locationAddress');
      const locationLink = document.getElementById('locationLink');

      if (data.attendance_method === 'حضوري') {
        // أيقونة موقع فقط
        iconDiv.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
        locationName.textContent = data.location_name || 'غير محدد';

        locationAddress.textContent = data.location_address || 'لا يوجد عنوان';
        locationAddress.href = data.location_address ? (data.location_address.startsWith('http') ? data.location_address : 'https://' + data.location_address) : '#';
        locationAddress.style.display = data.location_address ? 'inline-block' : 'none';

        locationLink.style.display = 'none'; // إخفاء زر الرابط
      } else {
        // أيقونة رابط فقط
        iconDiv.innerHTML = '<i class="fas fa-link"></i>';
        locationName.textContent = 'عن بعد';

        locationAddress.textContent = data.teams_link || 'لا يوجد رابط';
        locationAddress.href = data.teams_link || '#';
        locationAddress.style.display = data.teams_link ? 'inline-block' : 'none';

        locationLink.style.display = 'none'; // إخفاء زر الرابط لأنه تكرر أيقونة الرابط موجودة في locationAddress
      }

      const locationModal = new bootstrap.Modal(document.getElementById('locationModal'));
      locationModal.show();
    })
    .catch(() => {
      alert('فشل تحميل بيانات الموقع.');
    });
}




    // Add smooth scrolling and animations
    document.addEventListener('DOMContentLoaded', function() {
      // Animate cards on scroll
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };

      const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
          }
        });
      }, observerOptions);

      // Apply animation to cards
      document.querySelectorAll('.program-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
      });
    });
  </script>
</body>

</html>