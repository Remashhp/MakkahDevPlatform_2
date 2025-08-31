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
  <title>البرامج التدريبية</title>
  <link rel="stylesheet" href="CSSAdmin/BaseHedar.css">

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <!-- Flatpickr CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
  <!-- Flatpickr JS + اللغة العربية -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>



</head>




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

.btn-add-test {
  background-color: transparent;
  color: #6c757d; /* رمادي زي bootstrap */
  border: none;
  padding: 5px 10px;
  font-size: 14px;
   margin-right: 10px;
}

.btn-add-test i {
  color: #6c757d;
}

  .card-actions {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: auto;

  }

  .card-actions button {
    display: flex;
    align-items: center;
    gap: 1px;
    padding: 6px 12px;
    border: 1px solid;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    border-color: rgba(13, 169, 166, 1);
    background-color:  rgba(231, 244, 243, 1);
  }

  .btn-delete {
    color: #d9534f;
    border-color: #d9534f;
    margin-right: 10px;
  }

  /* أعد ضبط زريّ التعديل والمتدربين ليكونا بنفس الـ border والـ background */
  .btn-edit,
  .btn-trainees {
    border-color: rgba(13, 169, 166, 1);
    background-color: rgba(231, 244, 243, 1);
    color: rgba(110, 110, 110, 1);

  }

  .btn-trainees {
    margin-left: 10px;
    
    
  }

  /* أيقونات زر التعديل والـ trash تظل باللون الأبيض أو كما تريد */
  /* أيقونة المتدربين كن نسخة فارغة ولون رمادي */
  .btn-trainees i {
    color: #888;
    /* رمادي */
  }




  .popup {
    display: none;
    position: fixed;
    top: 15%;
    right: 50%;
    transform: translate(50%, -50%);
    padding: 20px 30px;
    border-radius: 12px;
    width: 500px;
    font-size: 20px;
    z-index: 9999;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    text-align: right;
    background-color: #fff;
  }

  .popup.show {
    display: block;
  }

  /* تأكيد الحذف */
  #confirmDeletePopup {
    border: 1px solid rgba(110, 110, 110, 1);
  }

  #confirmDeletePopup .info-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
    font-size: 28px;
    margin-left: 20px;
    background-color: #d9534f;
    color: #fff;
    border-radius: 50%;
  }

  .popup-actions {
    margin-top: 16px;
    display: flex;
    gap: 12px;
    justify-content: center;
    /* تمركز الأزرار */
  }

  #confirmDeletePopup .btn-delete {
    background: transparent;
    border: 1px solid #d9534f;
    color: #d9534f;
    padding: 8px 16px;
    border-radius: 6px;
  }

  #confirmDeletePopup .btn-edit {
    background: transparent;
    border: 1px solid #888;
    color: #888;
    padding: 8px 16px;
    border-radius: 6px;
  }

  /* إشعار نجاح الحذف */
  #deleteSuccessPopup {
    border: 1px solid #b3d8e8;
    color: #000033;
  }

  #deleteSuccessPopup .info-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    font-size: 24px;
    margin-left: 20px;
    background-color: #28a745;
    color: #fff;
    border-radius: 50%;
  }

  /* إشعار خطأ الحذف */
  #deleteErrorPopup {
    border: 1px solid #f5c2c0;
    color: #a94442;
  }

  #deleteErrorPopup .info-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
    font-size: 50px;
    margin-left: 20px;
    background-color: #d9534f;
    color: #fff;
    border-radius: 50%;
  }
</style>





<body>

  <!-- Popup تأكيد الحذف -->
  <div id="confirmDeletePopup" class="popup">
    <span class="info-icon">؟</span>
    هل أنت متأكد من حذف هذه الدورة؟
    <div class="popup-actions">
      <button id="confirmDeleteBtn" class="btn-delete">حذف</button>
      <button id="cancelDeleteBtn" class="btn-edit">إلغاء</button>
    </div>
  </div>

  <!-- إشعارات بعد عملية الحذف -->
  <div id="deleteSuccessPopup" class="popup">
    <span class="info-icon">✔</span> تم حذف الدورة بنجاح!
  </div>
  <div id="deleteErrorPopup" class="popup errorPopup">
    <span class="info-icon">!</span> فشل في حذف الدورة.
  </div>


  <div class="container">
    <aside class="sidebar">
      <img class="logo" src="imageAdmin/logoHome.png" alt="شعار الوزارة" />
      <div class="user-info">
        <div class="icon-circle">
          <i class="fas fa-user"></i>
        </div>
        <div class="username">أمل الحارثي</div>
      </div>

      <div class="username-line"></div>

      <a class="active" href="HomeAdmin.php">
        <img src="imageAdmin/home.svg" alt="أيقونة" />
        <b> الرئيسية </b>
      </a>
      
      <a href="SurveyForm.php">
        <img src="imageAdmin/surveys.svg" alt="أيقونة" />
        <b> تسجيل خروج </b>
      </a>

    </aside>

    <main class="main-content">
      <div class="header">
        <div class="welcome-container">
          <div class="icon-circle">
            <i class="fas fa-user"></i>
          </div>
          <div class="welcome">أهلاً أمل </div>
          <div class="header-divider"></div>
          <i class="far fa-envelope" style="font-size: 24px; color: #000;"></i>
        </div>
      </div>

      <div class="divider-section">
        <div class="divider-title" style="color: rgba(8, 105, 130, 1); font-size:35px ;">تابعي تطورات الدورات المسندة لك هنا  </div>
        <div class="short_line"></div>
        <div class="long_line"></div>
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
    <button class="btn-add-test">
  <i class="fas fa-file-medical"></i> اضافة اختبار
</button>

<button class="btn-edit" onclick="window.location.href='EditCourse.php?id=<?= $c['id'] ?>'">
  <i class="fas fa-file-alt"></i> التحضير
</button>

<button class="btn-trainees">
  <i class="far fa-user"></i> المتدربين
</button>

                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="no-courses">لا توجد دورات حالياً.</p>
        <?php endif; ?>
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






    // دالة موحدة لإظهار وخفاء البوب أب
    function showPopup(id) {
      const p = document.getElementById(id);
      p.classList.add('show');
      setTimeout(() => p.classList.remove('show'), 1500);
    }

    // تفويض حدث النقر على زر الحذف داخل حاوية البطاقات
    document.querySelector('.cards-container').addEventListener('click', e => {
      const delBtn = e.target.closest('.btn-delete');
      if (!delBtn) return;

      const card = e.target.closest('.course-card');
      const courseId = card.dataset.id;
      document.getElementById('confirmDeletePopup').classList.add('show');

      // زر التأكيد
      document.getElementById('confirmDeleteBtn').onclick = async () => {
        document.getElementById('confirmDeletePopup').classList.remove('show');
        try {
          const res = await fetch('deleteCourse.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              id: courseId
            })
          });
          const data = await res.json();
          if (data.success) {
            card.remove();
            showPopup('deleteSuccessPopup');
          } else {
            showPopup('deleteErrorPopup');
          }
        } catch {
          showPopup('deleteErrorPopup');
        }
      };

      // زر الإلغاء
      document.getElementById('cancelDeleteBtn').onclick = () => {
        document.getElementById('confirmDeletePopup').classList.remove('show');
      };
    });
  </script>




</body>





</html>