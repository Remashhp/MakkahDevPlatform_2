<?php
session_start();

// ——— عرض الأخطاء أثناء التطوير ———
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ——— اتصال بقاعدة البيانات باستخدام mysqli ———
include 'Connect.php';
$conn = new mysqli($host, $user, $password, $dbName);
if ($conn->connect_error) {
  die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
// ————————————————————————————————

$message = '';
$messageType = ''; // يمكن أن تكون 'success' أو 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // رفع وتخزين مسار الشهادة فقط
  if (isset($_POST['saveCertificate'])) {
    if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] === UPLOAD_ERR_OK) {
      $uploadDir = __DIR__ . '/uploads/';
      if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

      $timestamp = time();
      $certName  = $timestamp . '_cert_' . basename($_FILES['certificate']['name']);
      $certPath  = 'uploads/' . $certName;

      if (move_uploaded_file($_FILES['certificate']['tmp_name'], $uploadDir . $certName)) {
        $stmt = $conn->prepare(
          "INSERT INTO attachments (certificate, acceptance_notification) VALUES (?, NULL)"
        );
        $stmt->bind_param("s", $certPath);
        if ($stmt->execute()) {
          $message = "تم حفظ الشهادة بنجاح.";
          $messageType = "success";
        } else {
          $message = "حدث خطأ أثناء حفظ مسار الشهادة في قاعدة البيانات.";
          $messageType = "error";
        }
        $stmt->close();
      } else {
        $message = "فشل نقل ملف الشهادة إلى المجلد uploads/.";
        $messageType = "error";
      }
    } else {
      $message = "الرجاء اختيار ملف الشهادة قبل الحفظ.";
      $messageType = "error";
    }
  }

  // رفع وتخزين مسار إشعار القبول فقط
  if (isset($_POST['saveAcceptance'])) {
    if (isset($_FILES['acceptance']) && $_FILES['acceptance']['error'] === UPLOAD_ERR_OK) {
      $uploadDir = __DIR__ . '/uploads/';
      if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

      $timestamp = time();
      $admName   = $timestamp . '_acceptance_' . basename($_FILES['acceptance']['name']);
      $admPath   = 'uploads/' . $admName;

      if (move_uploaded_file($_FILES['acceptance']['tmp_name'], $uploadDir . $admName)) {
        $stmt = $conn->prepare(
          "INSERT INTO attachments (certificate, acceptance_notification) VALUES (NULL, ?)"
        );
        $stmt->bind_param("s", $admPath);
        if ($stmt->execute()) {
          $message = "تم حفظ إشعار القبول بنجاح.";
          $messageType = "success";
        } else {
          $message = "حدث خطأ أثناء حفظ مسار إشعار القبول في قاعدة البيانات.";
          $messageType = "error";
        }
        $stmt->close();
      } else {
        $message = "فشل نقل ملف إشعار القبول إلى المجلد uploads/.";
        $messageType = "error";
      }
    } else {
      $message = "الرجاء اختيار ملف إشعار القبول قبل الحفظ.";
      $messageType = "error";
    }
  }
}
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>المرفقات</title>
  <link rel="stylesheet" href="CSSAdmin/BaseHedar.css" />
  <!-- Font Awesome -->
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />






  <style>
    .attachments-wrapper {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      max-width: 900px;
      margin: 0 auto;
      padding: 20px 0;
    }

    @media (max-width: 768px) {
      .attachments-wrapper {
        grid-template-columns: 1fr;
      }
    }

    .attachment-section {
      text-align: center;
      font-family: Arial, sans-serif;
    }

    .section-title {
      font-weight: bold;
      font-size: 20px;
      color: #555;
      margin: 50px 0 15px;
      display: block;
    }

    .custom-upload {
      width: 80%;
      max-width: 500px;
      margin: auto;
      padding: 40px 20px;
      background: #f0fefa;
      border: 2px solid #bbb;
      border-radius: 10px;
      position: relative;
    }

    .upload-label {
      display: flex;
      flex-direction: column;
      align-items: center;
      cursor: pointer;
    }

    .upload-label i {
      font-size: 36px;
      color: #999;
      margin-bottom: 10px;
    }

    .upload-label span {
      font-size: 14px;
      color: #777;
    }

    .upload-label span u {
      color: #0DA9A6;
    }

    .remove-btn {
      display: none;
      background: none;
      border: none;
      color: red;
      cursor: pointer;
      font-size: 14px;
      margin-top: 10px;
    }

    .save-btn {
      display: block;
      margin: 20px auto;
      background: #0DA9A6;
      color: #fff;
      border: none;
      padding: 10px 40px;
      border-radius: 20px;
      cursor: pointer;
      font-size: 16px;
    }

    .message {
      margin: 20px auto;
      text-align: center;
      color: green;
      font-weight: bold;
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
    }

    .popup.show {
      display: block;
    }

    .popup.success {
      background: #e6f6fa;
      border: 1px solid #b3d8e8;
      color: #000033;
    }

    .popup.error {
      background: #fdeaea;
      border: 1px solid #f3b2b2;
      color: #b70000;
    }

    .popup .info-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      font-size: 28px;
      margin-left: 20px;
      border-radius: 50%;
    }

    .popup.success .info-icon {
      background: #28a745;
      color: #fff;
    }

    .popup.error .info-icon {
      background: #d32f2f;
      color: #fff;
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
          <div class="icon-circle"><i class="fas fa-user"></i></div>
          <div class="welcome">أهلاً شهد</div>
          <div class="header-divider"></div>
          <i class="far fa-envelope" style="font-size:24px;color:#000;"></i>
        </div>
      </div>

      <div class="divider-section">
        <div class="divider-title" style="color:rgba(8,105,130,1);font-size:35px;">المرفقات</div>
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
      <script src="test.js"></script>
      <!-- نهاية نافذة تسجيل الخروج -->




      <form method="post" enctype="multipart/form-data">
        <div class="attachments-wrapper">
          <!-- صندوق رفع الشهادة -->
          <div class="attachment-section">
            <label class="section-title">ارفق شهادة</label>
            <div class="custom-upload">
              <label class="upload-label" for="certificateFile">
                <i class="fas fa-cloud-upload-alt"></i>
                <span id="certText">اسحب الملفات هنا أو <u>تصفح الملفات</u></span>
              </label>
              <input type="file" name="certificate" id="certificateFile" accept="image/*,.pdf"
                onchange="handleFileUpload(event,'certText','removeCertBtn')" hidden />
              <button type="button" id="removeCertBtn" class="remove-btn"
                onclick="removeFile('certificateFile','certText','removeCertBtn')">
                حذف الملف
              </button>
            </div>
            <button type="submit" class="save-btn" name="saveCertificate">حفظ </button>
          </div>

          <!-- صندوق رفع إشعار القبول -->
          <div class="attachment-section">
            <label class="section-title">ارفق إشعار القبول</label>
            <div class="custom-upload">
              <label class="upload-label" for="acceptanceFile">
                <i class="fas fa-cloud-upload-alt"></i>
                <span id="acceptText">اسحب الملفات هنا أو <u>تصفح الملفات</u></span>
              </label>
              <input type="file" name="acceptance" id="acceptanceFile" accept="image/*,.pdf"
                onchange="handleFileUpload(event,'acceptText','removeAcceptBtn')" hidden />
              <button type="button" id="removeAcceptBtn" class="remove-btn"
                onclick="removeFile('acceptanceFile','acceptText','removeAcceptBtn')">
                حذف الملف
              </button>
            </div>
            <button type="submit" class="save-btn" name="saveAcceptance">حفظ </button>
          </div>
        </div>
      </form>





      <div id="successPopup" class="popup">
        <span class="info-icon" id="popupIcon"></span>
        <span id="successMsg"></span>
      </div>


    </main>
  </div>






  <script>
    // تفعيل العنصر النشط في الشريط الجانبي
    const links = document.querySelectorAll('.sidebar a');
    const currentPath = window.location.pathname.toLowerCase();
    links.forEach(link => {
      const href = link.getAttribute('href');
      if (!href) return;
      const normalized = new URL(href, window.location.origin).pathname.toLowerCase();
      link.classList.toggle('active', currentPath.endsWith(normalized));
    });

    // جافاسكربت لإدارة رفع وحذف الملفات
    function handleFileUpload(event, textId, btnId) {
      const file = event.target.files[0];
      const fileText = document.getElementById(textId);
      const removeBtn = document.getElementById(btnId);
      if (file) {
        fileText.innerHTML = `<b>${file.name}</b>`;
        removeBtn.style.display = 'inline-block';
      } else {
        fileText.innerHTML = 'اسحب هنا أو <u>تصفح</u>';
        removeBtn.style.display = 'none';
      }
    }

    function removeFile(inputId, textId, btnId) {
      document.getElementById(inputId).value = '';
      document.getElementById(textId).innerHTML = 'اسحب هنا أو <u>تصفح</u>';
      document.getElementById(btnId).style.display = 'none';
    }


    document.addEventListener('DOMContentLoaded', function() {
      const msg = '<?= addslashes($message) ?>';
      const type = '<?= $messageType ?>';

      if (msg) {
        document.getElementById('successMsg').textContent = msg;
        const popup = document.getElementById('successPopup');
        popup.classList.remove('success', 'error'); // إزالة الأنواع السابقة
        if (type === 'error') {
          popup.classList.add('error');
          document.getElementById('popupIcon').innerHTML = "&#9888;"; // ⚠️
        } else {
          popup.classList.add('success');
          document.getElementById('popupIcon').innerHTML = "&#10004;"; // ✔️
        }
        popup.classList.add('show');
        setTimeout(() => popup.classList.remove('show'), 1800);
      }
    });
  </script>
</body>

</html>