<?php
// adminAddCourse.php

// 1) عرض الأخطاء أثناء التطوير
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2) الاتصال بقاعدة البيانات
include 'Connect.php';

$conn = new mysqli($host, $user, $password, $dbName);
if ($conn->connect_error) {
  die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// 3) معالجة POST وإدخال السجلّ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // استقبال وتطهير المدخلات النصّية
  $title            = $conn->real_escape_string($_POST['course_title'] ?? '');
  $description      = $conn->real_escape_string($_POST['course_description'] ?? '');
  $requirements     = $conn->real_escape_string($_POST['course_requirements'] ?? '');
  $objectives       = $conn->real_escape_string($_POST['course_objectives'] ?? '');
  $duration         = $conn->real_escape_string($_POST['course_duration'] ?? '');
  $attendance       = $conn->real_escape_string($_POST['attendance_method'] ?? '');
  $teamsLink        = $conn->real_escape_string($_POST['teams_link'] ?? '');
  $locationName     = $conn->real_escape_string($_POST['location_name'] ?? '');
  $locationAddress  = $conn->real_escape_string($_POST['location_address'] ?? '');
  $organization     = $conn->real_escape_string($_POST['organization'] ?? '');
  $gender           = $conn->real_escape_string($_POST['gender'] ?? '');
  $seatCount        = intval($_POST['seat_count'] ?? 0);
  $startTime        = $conn->real_escape_string($_POST['start_time'] ?? '');
  $endTime          = $conn->real_escape_string($_POST['end_time'] ?? '');
  $startDate        = $conn->real_escape_string($_POST['start_date'] ?? '');
  $endDate          = $conn->real_escape_string($_POST['end_date'] ?? '');

  // رفع ملف الحقيبة التدريبية (training_material)
  $trainingMaterial = '';
  if (!empty($_FILES['training_material']['name']) && $_FILES['training_material']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/materials/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $fname      = time() . '_' . basename($_FILES['training_material']['name']);
    $targetFile = $uploadDir . $fname;
    if (move_uploaded_file($_FILES['training_material']['tmp_name'], $targetFile)) {
      $trainingMaterial = $conn->real_escape_string($targetFile);
    }
  }

  // رفع صورة الدورة
  $imagePath = '';
  if (!empty($_FILES['course_image']['name']) && $_FILES['course_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $filename   = time() . '_' . basename($_FILES['course_image']['name']);
    $targetFile = $uploadDir . $filename;
    if (move_uploaded_file($_FILES['course_image']['tmp_name'], $targetFile)) {
      $imagePath = $conn->real_escape_string($targetFile);
    }
  }

  // تحضير وتنفيذ الاستعلام
  $stmt = $conn->prepare(
    "INSERT INTO course_info
         (course_title, course_description, course_requirements, course_objectives,
          course_duration, attendance_method, teams_link, location_name, location_address,
          organization, gender, training_material, course_image, seat_count,
          start_time, end_time, start_date, end_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
  );
  $stmt->bind_param(
    "sssssssssssssissss",
    $title,
    $description,
    $requirements,
    $objectives,
    $duration,
    $attendance,
    $teamsLink,
    $locationName,
    $locationAddress,
    $organization,
    $gender,
    $trainingMaterial,
    $imagePath,
    $seatCount,
    $startTime,
    $endTime,
    $startDate,
    $endDate
  );

  if ($stmt->execute()) {
    // نبني JSON لكائن الدورة
    $courseData = json_encode([
      'title'     => $title,
      'image'     => $imagePath,
      'startDate' => $startDate,
      'duration'  => $duration
    ], JSON_HEX_APOS | JSON_HEX_QUOT);

    // طباعة سكربت يعرض البوب بعد DOMContentLoaded ثم يعيد التوجّه
    echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        const popup = document.getElementById('successPopup');
        if (popup) {
          popup.classList.add('show');
        } else {
          console.warn('Element #successPopup not found');
        }

        // لو تريد عرض البطاقة الجديدة قبل الخروج
        try {
          renderCourseCard($courseData);
        } catch (e) {
          console.error(e);
        }

        setTimeout(function() {
          if (popup) {
            popup.classList.remove('show');
          }
          window.location.href = 'HomeAddCourse.php';
        }, 1100);
      });
    </script>";
  }

  $stmt->close();
  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>إضافة دورة</title>
  <link rel="stylesheet" href="CSSAdmin/BaseHedar.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
  <!-- Flatpickr CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>إضافة دورة</title>
    <link rel="stylesheet" href="CSS Admin/BaseHedar.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">



    <style>
      body {
        font-family: "Sakkal Majalla", sans-serif;
        background-color: #f5f8f9;
        margin: 0;
      }

      /* ========= احذف هنا تعريفات popups القديمة (#successPopup, .show, .info-icon) ========= */

      /* ======= بقية التنسيقات كما هي ======= */

      .form-section {
        padding: 30px;
        border-radius: 15px;
        margin: 30px 0 40px auto;
        direction: rtl;
        text-align: right;
        max-width: 900px;
        margin-right: 40px;
      }

      .form-image {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 15px;
        margin-top: 10px;
      }

      .image-box {
        width: 160px;
        height: 120px;
        background-color: #B8DCE5;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        overflow: hidden;
        margin-bottom: 20px;
      }

      .image-box i {
        font-size: 32px;
        color: #333;
      }

      .image-box img {
        max-height: 100%;
        max-width: 100%;
        border-radius: 10px;
        object-fit: contain;
      }

      .delete-icon {
        background: none;
        border: none;
        font-size: 20px;
        color: black;
        cursor: pointer;
        padding: 0;
      }

      .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px 30px;
        text-align: right;
      }

      .form-group {
        display: flex;
        flex-direction: column;
      }

      .form-group label {
        font-weight: bold;
        font-size: 16px;
        margin-bottom: 5px;
        color: rgba(8, 105, 130, 1);
      }

      .form-group input,
      .form-group select,
      .form-group textarea {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-family: "Sakkal Majalla", sans-serif;
      }

      .form-group textarea {
        height: 100px;
        resize: none;
        box-sizing: border-box;
        padding: 10px 12px 10px 20px;
        overflow-y: auto;
        scrollbar-width: thin;
        line-height: 1.5;
      }

      .form-group textarea::-webkit-scrollbar {
        width: 5px;
        height: 5px;
      }

      .form-group textarea::-webkit-scrollbar-thumb {
        background-color: rgba(100, 100, 100, 0.4);
        border-radius: 6px;
      }

      .form-group textarea::-webkit-scrollbar-track {
        background-color: transparent;
      }

      .form-upload {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 2px dashed #0DA9A6;
        background-color: #fff;
        padding: 30px;
        border-radius: 12px;
        grid-column: 1 / -1;
      }

      .upload-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        cursor: pointer;
      }

      .upload-label i {
        font-size: 30px;
        color: #0DA9A6;
      }

      .form-actions {
        display: flex;
        gap: 20px;
        justify-content: center;
        grid-column: 1 / -1;
      }

      .form-actions.column-buttons {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 16px;
        margin-top: 30px;
      }

      .form-actions .submit {
        background-color: #0DA9A6;
        color: white;
        padding: 14px 40px;
        font-size: 18px;
        border: none;
        border-radius: 10px;
        width: 250px;
        cursor: pointer;
      }

      .form-actions .draft {
        background-color: rgba(231, 244, 243, 1);
        color: black;
        padding: 12px 32px;
        font-size: 16px;
        border: 2px solid rgba(13, 169, 166, 1);
        border-radius: 10px;
        width: 200px;
        cursor: pointer;
      }

      .attendance-box {
        display: flex;
        width: 100%;
        height: 42px;
        border: 1px solid #ccc;
        border-radius: 8px;
        overflow: hidden;
        background-color: #f2f2f2;
        box-sizing: border-box;
      }

      .attendance-option {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: "Sakkal Majalla", sans-serif;
        font-size: 16px;
        cursor: pointer;
        font-weight: bold;
        color: #333;
        transition: 0.3s;
        height: 100%;
      }

      .attendance-option.active {
        background-color: #0DA9A6;
        color: white;
        border-radius: 8px;
      }

      .seat-input {
        position: relative;
        width: 100%;
        max-width: 100%;
      }

      .seat-input input {
        width: 100%;
        padding: 10px 40px 10px 40px;
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-family: "Sakkal Majalla", sans-serif;
        appearance: none;
        -moz-appearance: textfield;
      }

      .seat-input input::-webkit-inner-spin-button,
      .seat-input input::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
      }

      .seat-input button {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: gray;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        width: 30px;
        height: 30px;
        line-height: 1;
        z-index: 2;
      }

      .seat-input button.minus {
        left: 10px;
      }

      .seat-input button.plus {
        right: 10px;
      }

      .custom-upload {
        height: 120px;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        border: 2px dashed #0DA9A6;
        background-color: #fff;
        width: 50%;
        max-width: 500px;
        margin-inline-start: 0;
        margin-inline-end: auto;
      }

      .custom-upload .upload-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        cursor: pointer;
      }

      .custom-upload .upload-label span {
        font-size: 16px;
        color: #555;
      }

      .custom-upload .upload-label i {
        font-size: 30px;
        color: #0DA9A6;
      }

      .date-wrapper {
        position: relative;
      }

      .date-wrapper input {
        width: 100%;
        padding-right: 2em;
      }

      .calendar-icon {
        position: absolute;
        top: 50%;
        right: 0.75em;
        transform: translateY(-50%);
        pointer-events: none;
        width: 1em;
        height: 1em;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
      }

      /* ======= تنسيقات البوب أب الجديدة ======= */

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

      #successPopup {
        background-color: #e6f6fa;
        border: 1px solid #b3d8e8;
        color: #000033;
      }

      #successPopup .info-icon {
        display: inline-flex;
        /* لتمكين المحاذاة الأفقية والعمودية */
        align-items: center;
        justify-content: center;
        width: 40px;
        /* زيادة العرض */
        height: 40px;
        /* زيادة الارتفاع */
        font-size: 24px;
        /* نص أكبر لعلامة ✔ */
        margin-left: 20px;
        /* مسافة أكبر بين الأيقونة والنص */
        background-color: #28a745;
        color: white;
        border-radius: 50%;
      }

      .errorPopup {
        background-color: #fdecea;
        border: 1px solid #f5c2c0;
        color: #a94442;
      }

      .errorPopup .info-icon {
        display: inline-flex;
        /* لتمكين المحاذاة الأفقية والعمودية */
        align-items: center;
        justify-content: center;
        width: 45px;
        /* زيادة العرض */
        height: 45px;
        /* زيادة الارتفاع */
        font-size: 50px;
        /* نص أكبر لعلامة التعجب */
        margin-left: 20px;
        /* مسافة أكبر بين الأيقونة والنص */
        background-color: #d9534f;
        color: white;
        border-radius: 50%;
      }
    </style>


  </head>


<body>
  <!-- popups الأخطاء والإشعارات -->
  <div id="dateErrorPopup" class="popup errorPopup">
    <span class="info-icon">!</span>
    تاريخ النهاية لا يمكن أن يسبق تاريخ البداية.
  </div>
  <div id="timeErrorPopup" class="popup errorPopup">
    <span class="info-icon">!</span>
    وقت النهاية لا يمكن أن يسبق وقت البداية.
  </div>
  <div id="emptyFieldsPopup" class="popup errorPopup">
    <span class="info-icon">!</span>
    الرجاء ملء جميع الحقول المطلوبة.
  </div>
  <div id="errorPopup" class="popup errorPopup">
    <span class="info-icon">!</span>
    حدث خطأ أثناء الإضافة.
  </div>

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
        <div class="divider-title" style="color:#0DA9A6;font-size:35px;">إضافة دورة</div>
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

      <section class="form-section">

        <!-- نموذج إضافة الدورة -->

        <form method="post" enctype="multipart/form-data" class="form-grid"
          onsubmit="return showSuccessPopup()">

          <!-- حقل مخفي لنقل قيمة attendance_method -->
          <input type="hidden" name="attendance_method" id="attendanceMethodInput" required>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label>صورة الدورة</label>

            <div class="form-image">
              <div class="image-box" onclick="document.getElementById('imageUpload').click()">
                <img id="courseImage" src="" alt="ارفق صورة الدورة" style="display: none; max-height: 80px;" />
                <i class="far fa-image" id="placeholderIcon"></i>
                <input type="file" name="course_image" id="imageUpload" accept="image/*" onchange="previewImage(event)"
                  hidden />
              </div>

              <button type="button" class="delete-icon" onclick="removeCourseImage()" required>
                <i class="far fa-trash-alt"></i>
              </button>
            </div>
          </div>


          <!-- معلومات أساسية -->
          <div class="form-group">
            <label>عنوان الدورة</label>
            <input type="text" placeholder="اسم الدورة" name="course_title" required />
          </div>
          <div class="form-group">
            <label>وصف الدورة</label>
            <textarea placeholder="وصف الدورة" name="course_description" style="height: 38px;" required></textarea>
          </div>
          <div class="form-group">
            <label>أهداف الدورة</label>
            <textarea placeholder="١- ..." name="course_objectives" onkeydown="autoNumberOnEnter(event, this)" style="height: 38px;"
              required></textarea>
          </div>
          <div class="form-group">
            <label>متطلبات الدورة</label>
            <textarea placeholder="١- ..." name="course_requirements" onkeydown="autoNumberOnEnter(event, this)"style="height: 38px;"
              required></textarea>
          </div>




          <hr
            style=" width: 60%; grid-column: 1 / -1; border: 0; height: 1px; background: linear-gradient(to left, rgba(200, 200, 200, 0.85), transparent); margin: 20px 0;">




          <!-- التواريخ والخيارات -->

          <div class="form-group">
            <label>تاريخ الدورة</label>
            <div style="display: flex; gap: 10px;">
              <div class="date-wrapper" style="flex: 1;">
                <input type="text" id="startDate" name="start_date" required />
                <!-- أيقونة SVG من Heroicons -->
                <svg xmlns="http://www.w3.org/2000/svg" class="calendar-icon" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 
                 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="date-wrapper" style="flex: 1;">
                <input type="text" id="endDate" name="end_date" required />
                <svg xmlns="http://www.w3.org/2000/svg" class="calendar-icon" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 
                 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
          </div>






          <div class="form-group">
            <label>وقت الدورة</label>
            <div style="display: flex; gap: 10px;">
              <input type="time" name="start_time" required />
              <input type="time" name="end_time" required />
            </div>
          </div>


          <div class="form-group">
            <label>آلية الحضور</label>
            <div class="attendance-box">
              <div id="inPerson"
                class="attendance-option active"
                onclick="selectAttendance('inPerson')">
                حضوري
              </div>
              <div id="remote"
                class="attendance-option"
                onclick="selectAttendance('remote')">
                عن بعد
              </div>
            </div>
          </div>



          <div class="form-group">
            <label>مدة الدورة</label>
            <select name="course_duration" required>
              <option selected disabled value="">انقر لاختيار مدة الدورة</option>
              <option>يوم</option>
              <option>يومين</option>
              <option> ثلاث أيام</option>
              <option>خمس أيام</option>
              <option>أسبوع</option>
              <option>شهر</option>
            </select>
          </div>




          <div class="form-group">
            <label id="locationLabel">اسم الموقع</label>
            <input type="text" id="locationName" name="location_name" placeholder="ادخل اسم الموقع" required />
          </div>





          <div class="form-group" id="addressGroup">
            <label id="addressLabel">عنوان الموقع</label>
            <input type="text" id="locationLink" name="location_address" placeholder="انسخ رابط الموقع هنا" required />
          </div>



          <hr
            style=" width: 60%; grid-column: 1 / -1; border: 0; height: 1px; background: linear-gradient(to left, rgba(200, 200, 200, 0.85), transparent); margin: 20px 0;">



          <div class="form-group">
            <label>جهة العمل</label>
            <select name="organization" required>
              <option selected disabled value="">اختر جهة العمل</option>
              <option>مسؤول قسم </option>
              <option>مسؤول مدرسة </option>
              <option> الجميع </option>
            </select>
          </div>



          <div class="form-group">
            <label>الجنس المستهدف</label>
            <select name="gender" required>
              <option selected disabled>اختر الجنس</option>
              <option>أنثى </option>
              <option>ذكر </option>
              <option> الجميع </option>

            </select>
          </div>


          <div class="form-group">
            <label>عدد المقاعد</label>
            <div class="seat-input">
              <button type="button" class="minus" onclick="changeSeats(-1)">−</button>
              <input type="number" name="seat_count" id="seatCount" value="1" min="1" max="500" required />
              <button type="button" class="plus" onclick="changeSeats(1)">+</button>
            </div>

          </div>


          <hr
            style=" width: 60%; grid-column: 1 / -1; border: 0; height: 1px; background: linear-gradient(to left, rgba(200, 200, 200, 0.85), transparent); margin: 20px 0;">


          <!-- الحقيبة التدريبية -->
          <div class="form-group" style="grid-column: 1 / -1;">
            <label style="margin-bottom: 10px;">الحقيبة التدريبية</label>
            <div class="form-upload custom-upload">
              <label class="upload-label" id="customUploadArea">
                <i class="fas fa-cloud-upload-alt"></i>
                <span id="fileText">اسحب الملفات هنا أو <u>تصفح الملفات</u></span>
                <input type="file" name="training_material" id="trainingFile" accept="application/pdf"
                  onchange="handleFileUpload(event)" hidden required />
              </label>

              <button type="button" id="removeFileBtn" onclick="removeFile()"
                style="display: none; margin-top: 10px; background: none; border: none; color: red; cursor: pointer;">حذف
                الملف</button>
            </div>
          </div>


          <div class="form-actions column-buttons">
            <button class="submit" type="submit" name="add_course" onclick="showSuccessPopup()">إضافة </button>
            <button class="draft" type="submit" name="add_course" onclick="showSuccessPopup()">مسودة</button>
          </div>

        </form>






        <!-- popup النجاح -->
        <div id="successPopup" class="popup">
          <span class="info-icon">✔</span>
          تم إضافة الدورة بنجاح!
        </div>
      </section>
    </main>
  </div>

  <!-- مكتبات flatpickr -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>
  <script>
    flatpickr("#startDate", {
      locale: "ar"
    });
    flatpickr("#endDate", {
      locale: "ar"
    });
  </script>




  <script src="test.js"></script>
  
  <script>
    // هنا خاصة بالناف بار 
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




    // خاصة بالصور
    function previewImage(event) {
      const reader = new FileReader();
      reader.onload = function() {
        const output = document.getElementById('courseImage');
        const placeholder = document.getElementById('placeholderIcon');
        output.src = reader.result;
        output.style.display = 'block';
        placeholder.style.display = 'none';
      };
      reader.readAsDataURL(event.target.files[0]);
    }


    function removeCourseImage() {
      const image = document.getElementById('courseImage');
      const placeholder = document.getElementById('placeholderIcon');
      const input = document.getElementById('imageUpload');

      image.src = '';
      image.style.display = 'none';
      placeholder.style.display = 'block';
      input.value = '';
    }




    // خاص بترقيم نقاط التكست اريا

function toArabicNumber(num) {
  const arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
  return String(num).split('').map(d => arabicDigits[d] || d).join('');
}

function autoNumberOnEnter(e, textarea) {
  if (e.key === 'Enter') {
    setTimeout(() => {
      const lines = textarea.value.split('\n');
      const numbered = lines.map((line, idx) => {
        const trimmed = line.trim().replace(/^([٠-٩\d]+[\-\.]?\s*)/, '');
        const arabicNum = toArabicNumber(idx + 1);
        return `${arabicNum}- ${trimmed}`;
      });
      const start = textarea.selectionStart;
      const end = textarea.selectionEnd;
      textarea.value = numbered.join('\n');
      textarea.setSelectionRange(start, end);
    }, 0); // بعد الإدخال مباشرة
  }
}




    // خاصة بعدد المقاعد
    function changeSeats(step) {
      const input = document.getElementById("seatCount");
      let value = parseInt(input.value) || 1;
      value += step;
      if (value < 1) value = 1;
      if (value > 100) value = 100;
      input.value = value;
    }







    // خاصةالملفات
    function handleFileUpload(event) {
      const fileInput = event.target;
      const file = fileInput.files[0];
      const fileText = document.getElementById('fileText');
      const removeBtn = document.getElementById('removeFileBtn');

      if (file && file.type === "application/pdf") {
        fileText.innerHTML = `<b>${file.name}</b>`;
        removeBtn.style.display = "inline-block";
      } else {
        alert("الرجاء اختيار ملف PDF فقط");
        fileInput.value = "";
        fileText.innerHTML = 'اسحب الملفات هنا أو <u>تصفح الملفات</u>';
        removeBtn.style.display = "none";
      }
    }

    function removeFile() {
      const fileInput = document.getElementById('trainingFile');
      const fileText = document.getElementById('fileText');
      const removeBtn = document.getElementById('removeFileBtn');

      fileInput.value = "";
      fileText.innerHTML = 'اسحب الملفات هنا أو <u>تصفح الملفات</u>';
      removeBtn.style.display = "none";
    }




    function selectAttendance(type) {
      const inPersonBtn = document.getElementById("inPerson");
      const remoteBtn = document.getElementById("remote");
      const label = document.getElementById("locationLabel");
      const locationName = document.getElementById("locationName");
      const addressGroup = document.getElementById("addressGroup");
      const locationLink = document.getElementById("locationLink");
      const attendanceInp = document.getElementById("attendanceMethodInput");

      // 1) حدِّد قيمة نوع الحضور
      attendanceInp.value = type === 'inPerson' ? 'حضوري' : 'عن بعد';

      if (type === "inPerson") {
        // حالة حضوري
        inPersonBtn.classList.add("active");
        remoteBtn.classList.remove("active");

        // حقول الموقع
        label.innerText = "اسم الموقع";
        locationName.name = "location_name";
        locationName.required = true;
        locationName.placeholder = "ادخل اسم الموقع";

        // حقول العنوان
        addressGroup.style.display = "flex";
        locationLink.name = "location_address";
        locationLink.required = true;
      } else {
        // حالة عن بعد
        remoteBtn.classList.add("active");
        inPersonBtn.classList.remove("active");

        // حقل التيمز
        label.innerText = "رابط التيمز";
        locationName.name = "teams_link";
        locationName.required = true;
        locationName.placeholder = "ادخل رابط التيمز";

        // إخفاء حقل العنوان وإلغاء التحقق عنه
        addressGroup.style.display = "none";
        locationLink.name = "";
        locationLink.required = false;
      }
    }




    function renderCourseCard(data) {
      const container = document.getElementById('coursesContainer');
      const card = document.createElement('div');
      card.className = 'course-card';
      card.innerHTML = `
    <img src="${data.image}" alt="صورة الدورة" class="course-card__image" />
    <div class="course-card__info">
      <h4>${data.title}</h4>
      <p>من ${data.startDate} لمدة ${data.duration}</p>
    </div>
  `;
      container.appendChild(card);
    }

    function showSuccessPopup() {
      ['successPopup', 'errorPopup', 'dateErrorPopup', 'timeErrorPopup', 'emptyFieldsPopup']
      .forEach(id => document.getElementById(id).classList.remove('show'), 1500);

      const sd = document.getElementById('startDate').value;
      const ed = document.getElementById('endDate').value;
      if (new Date(ed) < new Date(sd)) {
        const p = document.getElementById('dateErrorPopup');
        p.classList.add('show');
        setTimeout(() => p.classList.remove('show'), 1500);
        return false;
      }

      const st = document.querySelector('input[name="start_time"]').value;
      const et = document.querySelector('input[name="end_time"]').value;
      if (st && et) {
        const [sh, sm] = st.split(':').map(Number);
        const [eh, em] = et.split(':').map(Number);
        if (eh < sh || (eh === sh && em <= sm)) {
          const p = document.getElementById('timeErrorPopup');
          p.classList.add('show');
          setTimeout(() => p.classList.remove('show'), 1500);
          return false;
        }
      }

      let allFilled = true;
      document.querySelectorAll('input[required], textarea[required], select[required]')
        .forEach(f => {
          if (!f.value.trim()) allFilled = false;
        });
      if (!allFilled) {
        const p = document.getElementById('emptyFieldsPopup');
        p.classList.add('show');
        setTimeout(() => p.classList.remove('show'), 1500);
        return false;
      }

      return true;


    }
  </script>
</body>

</html>