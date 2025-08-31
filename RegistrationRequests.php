<?php
// HomeAddCourse.php

// --- 1) اتصال بقاعدة البيانات
include 'Connect.php';
// --- 2) جلب جميع الدورات مع المعرف
$sql    = "SELECT id, course_title, course_image, start_date, course_duration
           FROM course_info
           ORDER BY id DESC";
$result = $conn->query($sql);
?>




<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>سجل الطلبات</title>
  <link rel="stylesheet" href="CSSAdmin/BaseHedar.css">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <!-- Flatpickr CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
  <!-- Flatpickr JS + اللغة العربية -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>


  <style>
    #coursesContainer {
      display: flex;
      flex-wrap: wrap;
      justify-content: start;
      gap: 20px;
      margin-top: 30px;
      padding-right: 40px;
    }

    .program-card {
      width: 270px;
      border-radius: 35px;
      border: 1px solid rgba(110, 110, 110, 1);
      overflow: hidden;
      background-color: #fff;
      text-align: right;
      transition: transform 0.3s;
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

    .program-title {
      font-size: 29px;
      font-weight: bold;
      color: #222;
    }

    .program-info {
      color: #6E6E6E;
      font-size: 17px;
    }

    .program-info i {
      color: #a0a0a0;

    }


    .cards-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 10px;
      padding: 20px;
    }

    .course-card {
      background: #fff;
      border: 1px solid #e0e0e0;
      border-radius: 12px;
      overflow: hidden;
      border: 1px solid rgba(110, 110, 110, 1);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
      display: flex;
      flex-direction: column;
    }

    .card-image img {
      width: 100%;
      height: 150px;
      object-fit: cover;
      display: block;
      border-radius: 12px;

    }

    .card-body {
      padding: 16px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .card-title {
      margin: 0;
      font-size: 18px;
      font-weight: bold;
      color: #000;
    }

    .card-meta {
      display: flex;
      justify-content: space-between;
      margin: 12px 0;
      color: #666;
      font-size: 14px;
    }

    .meta-item {
      display: flex;
      align-items: center;
    }

    .meta-item i {
      margin-left: 6px;
      color: #888;
    }


    /* تنسيقات بوتن طلبات  التسجيل*/

    .card-actions {
      display: flex;
      justify-content: center;
      margin-top: auto;

    }

    .card-actions button {
      display: flex;
      align-items: center;
      padding: 6px 60px;
      border: 1px solid;
      border-radius: 6px;
      font-size: 14px;
      cursor: pointer;
      border-color: rgba(13, 169, 166, 1);
      background-color: rgba(231, 244, 243, 1);
    }



    .btn-Request {
      border-color: rgba(13, 169, 166, 1);
      background-color: rgba(231, 244, 243, 1);
      color: rgba(110, 110, 110, 1);

    }
  </style>

</head>




<body>




  <div class="container">
    <aside class="sidebar">
      <img class="logo" src="imageAdmin/logoHome.png" alt="شعار الوزارة" />
      <div class="user-info">
        <div class="icon-circle">
          <i class="fas fa-user"></i>
        </div>
        <div class="username">شهد القرشي</div>
      </div>
      <div class="username-line"></div>

      <a class="active" href="HomeAdmin.php">
        <img src="imageAdmin/home.svg" alt="أيقونة" />
        <b> الرئيسية </b>
      </a>
      <a href="HomeAddCourse.php">
        <img src=" imageAdmin/AddCourse.svg" alt="أيقونة" />
        <b> اضافة دورة </b>
      </a>
      <a href="RegistrationRequests.php">
        <img src="imageAdmin/request log.svg" alt="أيقونة" />
        <b> سجل الطلبات </b>
      </a>
      <a href="Attachments.php">
        <img src="imageAdmin/files.svg" alt="أيقونة" />
        <b> المرفقات </b>
      </a>
      <a href="AssignTrainer.php">
        <img src="imageAdmin/coach.svg" alt="أيقونة" />
        <b> تعيين مدرب </b>
      </a>
      <a href="Users.php">
        <img src="imageAdmin/AllUser.svg" alt="أيقونة" />
        <b> المستخدمين </b>
      </a>
      <a href="SurveyForm.php">
        <img src="imageAdmin/surveys.svg" alt="أيقونة" />
        <b> نموذج الاستبيان </b>
      </a>

      <button id="logout-link" type="button" style="background: none; border: none; cursor: pointer; display: flex; align-items: center; width: 100%; padding: 10px 10px 10px 20px; margin: 10px 0; color: inherit; font: inherit; gap:10px;">
        <img src="imageAdmin/log-out.svg" alt="أيقونة" />
        <b> تسجيل خروج </b>
      </button>

    </aside>



    <main class="main-content">
      <div class="header">
        <div class="welcome-container">
          <div class="icon-circle">
            <i class="fas fa-user"></i>
          </div>
          <div class="welcome">أهلاً شهد</div>
          <div class="header-divider"></div>
          <i class="far fa-envelope" style="font-size: 24px; color: #000;"></i>
        </div>
      </div>

      <div class="divider-section">
        <div class="divider-title" style="color: rgba(8, 105, 130, 1); font-size:35px ;"> سجل الطلبات</div>
        <div class="short_line"></div>
        <div class="long_line"></div>
      </div>

      <!-- نافذة تسجيل الخروج -->
      <div id="logoutModal" class="logout-modal-bg" tabindex="-1">
        <div class="logout-modal" role="dialog" aria-modal="true" aria-labelledby="logoutModalLabel">
          <div class="logout-modal-header">
            تسجيل الخروج
            <button class="logout-modal-close" onclick="closeLogoutModal()" title="إغلاق"
              aria-label="إغلاق">&times;</button>
          </div>
          <div class="logout-modal-content">
            هل انت متأكد من رغبتك بتسجيل الخروج ؟
          </div>
          <div class="logout-modal-actions">
            <button class="btn-cancel" onclick="closeLogoutModal()">إلغاء</button>
            <button class="btn-logout" onclick="logoutRedirect()">
              <i class="fas fa-sign-out-alt"></i>
              تسجيل الخروج
            </button>
          </div>
        </div>
      </div>
      <script src="test.js"></script>
      <!-- نهاية نافذة تسجيل الخروج -->








      <!-- ✅ فلاتر البحث والتاريخ + زر الإضافة (بمسافات ضيقة وبدون أيقونة التاريخ) -->
      <div id="filtersSection"
        style="display: flex; flex-wrap: wrap; justify-content: flex-end; align-items: center; gap: 8px; margin: 30px 40px 20px 40px; flex-direction: row-reverse;">



        <!-- التاريخ -->
        <!-- غلاف الحقل مع أيقونة التقويم -->
        <div style="position: relative; width: 160px;">
          <input type="text" id="filterDate" placeholder=" التاريخ" autocomplete="off" style=" width: 100%; padding: 10px 40px 10px 10px; /* padding-right كبير كفاية للأيقونة */ border: 1px solid rgba(133, 135, 135, 1); border-radius: 12px; font-size: 15px; " />

          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="position: absolute; top: 50%; right: 12px;  transform: translateY(-50%);  width: 20px; height: 20px; stroke: rgba(133, 135, 135, 1); fill: none; stroke-width: 2; pointer-events: none; ">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5 a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>

        </div>

        <!-- البحث -->
        <div style="position: relative; flex: 1; max-width: 260px;">
          <i class="fas fa-search" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color:rgba(133, 135, 135, 1) ;"></i>
          <input type="text" id="searchInput" placeholder="البحث عن اسم الدورة" style="width: 100%; padding: 10px 36px 10px 10px; border: 1px solid rgba(133, 135, 135, 1) ; border-radius: 12px; font-size: 15px;" />
        </div>
      </div>










      <div class="cards-container">
        <?php if ($result && $result->num_rows): ?>
          <?php while ($c = $result->fetch_assoc()): ?>
            <?php
            $id        = intval($c['id']);
            $title     = htmlspecialchars($c['course_title']);
            $labelDate = date('j/n/Y', strtotime($c['start_date']));
            ?>
            <div class="course-card"
              data-id="<?= $id ?>"
              data-title="<?= mb_strtolower($title, 'UTF-8') ?>"
              data-date="<?= date('Y-m-d', strtotime($c['start_date'])) ?>">
              <div class="card-image">
                <img src="<?= htmlspecialchars($c['course_image']) ?>" alt="صورة الدورة">
              </div>
              <div class="card-body">
                <h3 class="card-title"><?= $title ?></h3>
                <div class="card-meta">
                  <div class="meta-item">
                    <i class="far fa-calendar-alt"></i> يبدأ من: <?= $labelDate ?>
                  </div>
                  <div class="meta-item">
                    <i class="far fa-clock"></i> المدة: <?= htmlspecialchars($c['course_duration']) ?>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="btn-Request" data-id="<?= $id ?>">طلبات التسجيل</button>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="no-courses">لا توجد دورات حالياً.</p>
        <?php endif; ?>
      </div>































































  </div>
  </main>
  </div>

  <script>
    const links = document.querySelectorAll('.sidebar a');
    const currentPath = window.location.pathname.toLowerCase();

    links.forEach(link => {
      const href = link.getAttribute('href');
      if (!href) return;

      const normalizedHref = new URL(href, window.location.origin).pathname.toLowerCase();

      if (currentPath.endsWith(normalizedHref)) {
        link.classList.add('active');
      } else {
        link.classList.remove('active');
      }
    });







    flatpickr("#filterDate", {
      locale: "ar",
      dateFormat: "Y-m-d"
    });





    // فلترة
    const searchInput = document.getElementById('searchInput'),
      dateInput = document.getElementById('filterDate');

    function filterCourses() {
      const nf = searchInput.value.trim().toLowerCase(),
        df = dateInput.value;
      document.querySelectorAll('.course-card').forEach(c => {
        const t = c.dataset.title,
          d = c.dataset.date;
        c.style.display = ((!nf || t.includes(nf)) && (!df || d === df)) ? '' : 'none';
      });
    }
    searchInput.addEventListener('input', filterCourses);
    dateInput.addEventListener('change', filterCourses);









    document.querySelectorAll('.btn-Request').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var courseId = this.getAttribute('data-id');
        window.location.href = 'eligibleTrainees.php?course_id=' + courseId;
      });
    });
  </script>


</body>

</html>