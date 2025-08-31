<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>لوحة تحكم الموارد البشرية</title>
  <link rel="stylesheet" href="CSSAdmin/BaseHedar.css">

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />

  <style>
    .stats {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      justify-content: center;
      margin: 30px 0;
    }

    .stat-box {
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
      padding: 20px;
      width: 180px;
      text-align: center;
    }

    .stat-box h2 {
      margin: 0;
      color: #00b0b5;
    }

    .charts-section {
      margin-top: 30px;
      padding: 0 20px;
    }

    .charts-section h2 {
      margin-bottom: 20px;
      color: rgba(8, 105, 130, 1);
      text-align: right;
      margin-right: 30px;
    }

    .charts {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
      margin-right: 30px;
    }

    .chart-placeholder {
      background-color: #fff;
      border: 1px solid #ccc;
      border-radius: 8px;
      height: 200px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      color: #888;
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
        <div class="divider-title" style="color: rgba(8, 105, 130, 1); font-size:35px ;">تابعي تطورات البوابة هنا</div>
        <div class="short_line"></div>
        <div class="long_line"></div>
      </div>


      <!-- نافذة تسجيل الخروج -->
      <div id="logoutModal" class="logout-modal-bg" tabindex="-1">
        <div class="logout-modal" role="dialog" aria-modal="true" aria-labelledby="logoutModalLabel">
          <div class="logout-modal-header">
            تسجيل الخروج
            <button class="logout-modal-close" onclick="closeLogoutModal()" title="إغلاق" aria-label="إغلاق">&times;</button>
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
      <!-- نهاية نافذة تسجيل الخروج -->


      <div class="stats">
        <div class="stat-box">
          <h2>25K</h2>
          <p>Today Views</p>
        </div>
        <div class="stat-box">
          <h2>220</h2>
          <p>Completed Tour</p>
        </div>
        <div class="stat-box">
          <h2>15K</h2>
          <p>Active User</p>
        </div>
      </div>

      <div class="charts-section">
        <h2>إحصائيات المتدربين</h2>
        <hr
          style="border: 0; height: 1px; background: linear-gradient(to left, rgba(200, 200, 200, 0.85), transparent); margin: 20px 0;">
        <div class="charts">
          <div class="chart-placeholder">رسم بياني 1</div>
          <div class="chart-placeholder">رسم بياني 2</div>
          <div class="chart-placeholder">رسم بياني 3</div>
        </div>

        <h2 style="margin-top: 40px;">إحصائيات التقييمات</h2>
        <hr
          style="border: 0; height: 1px; background: linear-gradient(to left, rgba(200, 200, 200, 0.85), transparent); margin: 20px 0;">
        <div class="charts">
          <div class="chart-placeholder">رسم بياني 1</div>
          <div class="chart-placeholder">رسم بياني 2</div>
          <div class="chart-placeholder">رسم بياني 3</div>
        </div>
      </div>
    </main>
  </div>




  <script src="test.js"></script>

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
  </script>






</body>

</html>