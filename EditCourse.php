<?php
session_start();
// الاتصال بقاعدة البيانات
include 'Connect.php';

// جلب بيانات الدورة إذا كان هناك id في الرابط
$row = null;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM course_info WHERE id=$id LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
    }
}

// ====== 1+2+3+4+5 حلول كاملة ======================
if (isset($_POST['add_course'])) {
    $id = intval($_POST['course_id']);
    $course_title = $_POST['course_title'];
    $course_description = $_POST['course_description'];
    $course_objectives = $_POST['course_objectives'];
    $course_requirements = $_POST['course_requirements'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $attendance_method = $_POST['attendance_method'];
    $course_duration = $_POST['course_duration'];

    // عند الحضور: location_name و location_address
    // عند عن بعد: teams_link
    $location_name = isset($_POST['location_name']) ? $_POST['location_name'] : '';
    $location_address = isset($_POST['location_address']) ? $_POST['location_address'] : '';
    $teams_link = isset($_POST['teams_link']) ? $_POST['teams_link'] : '';
    $organization = $_POST['organization'];
    $gender = $_POST['gender'];
    $seat_count = $_POST['seat_count'];
    $change_notice = isset($_POST['change_notice']) ? $_POST['change_notice'] : '';

    // --- 2. التعامل مع الصورة ---
    $image_path = $row ? $row['course_image'] : '';
    $remove_image = isset($_POST['remove_course_image']) && $_POST['remove_course_image'] == "1";
    if ($remove_image && $image_path && file_exists($image_path)) {
        unlink($image_path); // حذف الصورة من السيرفر
        $image_path = '';
    }
    if (isset($_FILES['course_image']) && $_FILES['course_image']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['course_image']['tmp_name'];
        $ext = pathinfo($_FILES['course_image']['name'], PATHINFO_EXTENSION);
        $new_name = "uploads/course_images/" . uniqid('course_', true) . "." . $ext;
        if (!file_exists('uploads/course_images')) mkdir('uploads/course_images', 0777, true);
        move_uploaded_file($tmp_name, $new_name);
        // حذف القديمة إذا موجودة
        if ($image_path && file_exists($image_path)) unlink($image_path);
        $image_path = $new_name;
    }

    // --- 5. التعامل مع الحقيبة التدريبية (ملف PDF) ---
    $material_path = $row ? $row['training_material'] : '';
    $remove_file = isset($_POST['remove_training_file']) && $_POST['remove_training_file'] == "1";
    if ($remove_file && $material_path && file_exists($material_path)) {
        unlink($material_path);
        $material_path = '';
    }
    if (isset($_FILES['training_material']) && $_FILES['training_material']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['training_material']['tmp_name'];
        $ext = pathinfo($_FILES['training_material']['name'], PATHINFO_EXTENSION);
        $new_name = "uploads/training_materials/" . uniqid('material_', true) . "." . $ext;
        if (!file_exists('uploads/training_materials')) mkdir('uploads/training_materials', 0777, true);
        move_uploaded_file($tmp_name, $new_name);
        // حذف القديمة إذا موجودة
        if ($material_path && file_exists($material_path)) unlink($material_path);
        $material_path = $new_name;
    }

    // تصحيح: إذا حضوري فقط احفظ location_name/location_address, وإذا عن بعد فقط teams_link
    if ($attendance_method == 'عن بعد') {
        $location_name = '';
        $location_address = '';
    } else {
        $teams_link = '';
    }

    // تحديث الدورة
    $stmt = $conn->prepare(
        "UPDATE course_info SET course_title=?, course_description=?, course_requirements=?, course_objectives=?, course_duration=?, attendance_method=?, teams_link=?, location_name=?, location_address=?, organization=?, gender=?, seat_count=?, start_time=?, end_time=?, start_date=?, end_date=?, Change_Notice=?, course_image=?, training_material=? WHERE id=?"
    );
    $stmt->bind_param(
        "sssssssssssssssssssi",
        $course_title,
        $course_description,
        $course_requirements,
        $course_objectives,
        $course_duration,
        $attendance_method,
        $teams_link,
        $location_name,
        $location_address,
        $organization,
        $gender,
        $seat_count,
        $start_time,
        $end_time,
        $start_date,
        $end_date,
        $change_notice,
        $image_path,
        $material_path,
        $id
    );

    if ($stmt->execute()) {
        // 1. إعادة التوجيه مع success=1
        header("Location: HomeAddCourse.php?success=1");
        exit;
    } else {
        $_SESSION['error_edit'] = 1;
    }
}
?>














<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>تعديل الدورة</title>
    <link rel="stylesheet" href="CSSAdmin/BaseHedar.css" />
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


        .custom-multiselect {
            position: relative;
            user-select: none;
        }

        .select-box {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 10px;
            background-color: white;
            cursor: pointer;
            font-family: "Sakkal Majalla", sans-serif;
        }

        .checkbox-options {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 100%;
            z-index: 10;
            display: none;
            padding: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .checkbox-options label {
            display: block;
            margin-bottom: 8px;
            cursor: pointer;
            font-size: 16px;
            color: #000;
        }
    </style>


</head>


<body>
    <!-- البوب أب -->
    <div id="successPopup" class="popup">
        <span class="info-icon">✔</span>
        تم تعديل الدورة بنجاح!
    </div>
    <div id="errorPopup" class="popup errorPopup">
        <span class="info-icon">!</span>
        حدث خطأ أثناء تعديل الدورة.
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
                <div class="divider-title" style="color:#0DA9A6;font-size:35px;">تعديل الدورة</div>
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
                <form method="post" enctype="multipart/form-data" class="form-grid" onsubmit="return showSuccessPopup()">
                    <input type="hidden" name="course_id" value="<?= $row ? $row['id'] : '' ?>">

                    <!-- 2. صورة الدورة -->
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>صورة الدورة</label>
                        <div class="form-image">
                            <div class="image-box" onclick="document.getElementById('imageUpload').click()">
                                <img id="courseImage" src="<?= $row && !empty($row['course_image']) ? htmlspecialchars($row['course_image']) : '' ?>"
                                    alt="ارفق صورة الدورة"
                                    style="<?= ($row && !empty($row['course_image'])) ? 'display:block;max-height:80px;' : 'display:none;max-height:80px;' ?>" />
                                <i class="far fa-image" id="placeholderIcon" style="<?= ($row && !empty($row['course_image'])) ? 'display:none;' : 'display:block;' ?>"></i>
                                <input type="file" name="course_image" id="imageUpload" accept="image/*" onchange="previewImage(event)" hidden />
                            </div>
                            <button type="button" class="delete-icon" onclick="removeCourseImage()"><i class="far fa-trash-alt"></i></button>
                        </div>
                        <input type="hidden" name="remove_course_image" id="removeCourseImageField" value="0" />
                    </div>

                    <!-- معلومات أساسية -->
                    <div class="form-group">
                        <label>عنوان الدورة</label>
                        <input type="text" placeholder="اسم الدورة" name="course_title"
                            value="<?= $row ? htmlspecialchars($row['course_title']) : '' ?>" required />
                    </div>

                    <div class="form-group">
                        <label>وصف الدورة</label>
                        <textarea placeholder="وصف الدورة" name="course_description" style="height: 38px;" required><?= $row ? htmlspecialchars($row['course_description']) : '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>أهداف الدورة</label>
                        <textarea placeholder="١- ..." name="course_objectives"  onkeydown="autoNumberOnEnter(event, this)"  style="height: 38px;" required><?= $row ? str_replace(['\\r\\n', '\\n', '\\r'], "\n", $row['course_objectives']) : '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>متطلبات الدورة</label>
                        <textarea placeholder="١- ..." name="course_requirements" onkeydown="autoNumberOnEnter(event, this)" style="height: 38px;" required><?= $row ? str_replace(['\\r\\n', '\\n', '\\r'], "\n", $row['course_requirements']) : '' ?></textarea>
                    </div>

                    <hr style=" width: 60%; grid-column: 1 / -1; border: 0; height: 1px; background: linear-gradient(to left, rgba(200, 200, 200, 0.85), transparent); margin: 20px 0;">

                    <!-- التواريخ والخيارات -->
                    <div class="form-group">
                        <label>تاريخ الدورة</label>
                        <div style="display: flex; gap: 10px;">
                            <div class="date-wrapper" style="flex: 1;">
                                <input type="text" id="startDate" name="start_date"
                                    value="<?= $row ? htmlspecialchars($row['start_date']) : '' ?>" required />
                                <!-- أيقونة SVG ... -->
                            </div>
                            <div class="date-wrapper" style="flex: 1;">
                                <input type="text" id="endDate" name="end_date"
                                    value="<?= $row ? htmlspecialchars($row['end_date']) : '' ?>" required />
                                <!-- أيقونة SVG ... -->
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>وقت الدورة</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="time" name="start_time"
                                value="<?= $row ? htmlspecialchars($row['start_time']) : '' ?>" required />
                            <input type="time" name="end_time"
                                value="<?= $row ? htmlspecialchars($row['end_time']) : '' ?>" required />
                        </div>
                    </div>

                    <div class="form-group">
                        <label>آلية الحضور</label>
                        <div class="attendance-box">
                            <div id="inPerson"
                                class="attendance-option<?= ($row && $row['attendance_method'] == 'حضوري') ? ' active' : '' ?>"
                                onclick="selectAttendance('inPerson')">
                                حضوري
                            </div>
                            <div id="remote"
                                class="attendance-option<?= ($row && $row['attendance_method'] == 'عن بعد') ? ' active' : '' ?>"
                                onclick="selectAttendance('remote')">
                                عن بعد
                            </div>
                        </div>
                        <input type="hidden" name="attendance_method" id="attendanceMethodInput"
                            value="<?= $row ? htmlspecialchars($row['attendance_method']) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label>مدة الدورة</label>
                        <select name="course_duration" required>
                            <option disabled value="">انقر لاختيار مدة الدورة</option>
                            <option <?= ($row && $row['course_duration'] == 'يوم') ? 'selected' : '' ?>>يوم</option>
                            <option <?= ($row && $row['course_duration'] == 'يومين') ? 'selected' : '' ?>>يومين</option>
                            <option <?= ($row && $row['course_duration'] == 'ثلاث أيام') ? 'selected' : '' ?>>ثلاث أيام</option>
                            <option <?= ($row && $row['course_duration'] == 'خمس أيام') ? 'selected' : '' ?>>خمس أيام</option>
                            <option <?= ($row && $row['course_duration'] == 'أسبوع') ? 'selected' : '' ?>>أسبوع</option>
                            <option <?= ($row && $row['course_duration'] == 'شهر') ? 'selected' : '' ?>>شهر</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label id="locationLabel"><?= $row && $row['attendance_method'] == 'عن بعد' ? 'رابط التيمز' : 'اسم الموقع' ?></label>
                        <input type="text" id="locationName" name="<?= $row && $row['attendance_method'] == 'عن بعد' ? 'teams_link' : 'location_name' ?>"
                            placeholder="<?= $row && $row['attendance_method'] == 'عن بعد' ? 'ادخل رابط التيمز' : 'ادخل اسم الموقع' ?>"
                            value="<?= $row && $row['attendance_method'] == 'عن بعد' ? htmlspecialchars($row['teams_link']) : htmlspecialchars($row['location_name']) ?>" required />
                    </div>

                    <div class="form-group" id="addressGroup" style="<?= $row && $row['attendance_method'] == 'عن بعد' ? 'display:none;' : '' ?>">
                        <label id="addressLabel">عنوان الموقع</label>
                        <input type="text" id="locationLink" name="location_address"
                            placeholder="انسخ رابط الموقع هنا"
                            value="<?= $row ? htmlspecialchars($row['location_address']) : '' ?>"
                            <?= $row && $row['attendance_method'] == 'عن بعد' ? '' : 'required' ?> />
                    </div>

                    <hr style=" width: 60%; grid-column: 1 / -1; border: 0; height: 1px; background: linear-gradient(to left, rgba(200, 200, 200, 0.85), transparent); margin: 20px 0;">

                    <div class="form-group">
                        <label>جهة العمل</label>
                        <select name="organization" required>
                            <option disabled value="">اختر جهة العمل</option>
                            <option <?= ($row && $row['organization'] == 'مسؤول قسم') ? 'selected' : '' ?>>مسؤول قسم</option>
                            <option <?= ($row && $row['organization'] == 'مسؤول مدرسة') ? 'selected' : '' ?>>مسؤول مدرسة</option>
                            <option <?= ($row && $row['organization'] == 'الجميع') ? 'selected' : '' ?>>الجميع</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>الجنس المستهدف</label>
                        <select name="gender" required>
                            <option disabled>اختر الجنس</option>
                            <option <?= ($row && $row['gender'] == 'أنثى') ? 'selected' : '' ?>>أنثى</option>
                            <option <?= ($row && $row['gender'] == 'ذكر') ? 'selected' : '' ?>>ذكر</option>
                            <option <?= ($row && $row['gender'] == 'الجميع') ? 'selected' : '' ?>>الجميع</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>عدد المقاعد</label>
                        <div class="seat-input">
                            <button type="button" class="minus" onclick="changeSeats(-1)">−</button>
                            <input type="number" name="seat_count" id="seatCount"
                                value="<?= $row ? (int)$row['seat_count'] : 1 ?>" min="1" max="500" required />
                            <button type="button" class="plus" onclick="changeSeats(1)">+</button>
                        </div>
                    </div>






                    <!-- إعلام بالتعديل (checkbox) -->
                    <div class="form-group">
                        <label>إعلام بالتعديل</label>
                        <div class="custom-multiselect" onclick="toggleDropdown()">
                            <?php
                            $selectedNotices = $row && !empty($row['Change_Notice']) ? explode(',', $row['Change_Notice']) : [];
                            ?>
                            <div class="select-box" id="selectedOptions">
                                <?= $selectedNotices ? implode("، ", $selectedNotices) : "انقر هنا لاختيار التعديل" ?>
                            </div>
                            <div class="checkbox-options" id="dropdownOptions">
                                <?php
                                $noticeOptions = ["تغيير التاريخ", "تغيير آلية الحضور", "تغيير المكان", "تغيير رابط الاجتماع"];
                                foreach ($noticeOptions as $notice) {
                                    $checked = in_array($notice, $selectedNotices) ? 'checked' : '';
                                    echo "<label><input type='checkbox' value='$notice' $checked> إعلام بـ$notice</label>";
                                }
                                ?>
                            </div>
                            <input type="hidden" name="change_notice" id="changeNoticeInput"
                                value="<?= $row && !empty($row['Change_Notice']) ? htmlspecialchars($row['Change_Notice']) : '' ?>" />
                        </div>
                    </div>

                    <hr style=" width: 60%; grid-column: 1 / -1; border: 0; height: 1px; background: linear-gradient(to left, rgba(200, 200, 200, 0.85), transparent); margin: 20px 0;">


                    <!-- الحقيبة التدريبية -->
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label style="margin-bottom: 10px;">الحقيبة التدريبية</label>
                        <div class="form-upload custom-upload">
                            <label class="upload-label" id="customUploadArea">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span id="fileText">
                                    <?php
                                    if ($row && !empty($row['training_material'])) {
                                        echo "<b>" . htmlspecialchars(basename($row['training_material'])) . "</b>";
                                    } else {
                                        echo "اسحب الملفات هنا أو <u>تصفح الملفات</u>";
                                    }
                                    ?>
                                </span>
                                <input type="file" name="training_material" id="trainingFile" accept="application/pdf"
                                    onchange="handleFileUpload(event)" hidden />
                            </label>
                            <button type="button" id="removeFileBtn" onclick="removeFile()"
                                style="<?= ($row && !empty($row['training_material'])) ? 'display:inline-block;' : 'display:none;' ?>margin-top: 10px; background: none; border: none; color: red; cursor: pointer;">حذف
                                الملف</button>
                            <input type="hidden" name="remove_training_file" id="removeTrainingFileField" value="0" />
                        </div>
                    </div>

                    <div class="form-actions column-buttons">
                        <button class="submit" type="submit" name="add_course">حفظ التعديل </button>
                        <button type="button" class="draft" onclick="window.location.href='HomeAddCourse.php'"> رجوع</button>
                    </div>

                </form>







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

        // عند حذف الصورة:
        function removeCourseImage() {
            document.getElementById('courseImage').src = '';
            document.getElementById('courseImage').style.display = 'none';
            document.getElementById('placeholderIcon').style.display = 'block';
            document.getElementById('imageUpload').value = '';
            document.getElementById('removeCourseImageField').value = "1";
        }


// تحويل الأرقام إلى الأرقام العربية
function toArabicNumber(num) {
  const arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
  return String(num).split('').map(d => arabicDigits[d] || d).join('');
}

//الترقيم في التيكست فيلد
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

        // عند حذف ملف الحقيبة
        function removeFile() {
            document.getElementById('trainingFile').value = "";
            document.getElementById('fileText').innerHTML = 'اسحب الملفات هنا أو <u>تصفح الملفات</u>';
            document.getElementById('removeFileBtn').style.display = "none";
            document.getElementById('removeTrainingFileField').value = "1";
        }




        function selectAttendance(type) {
            const inPersonBtn = document.getElementById("inPerson");
            const remoteBtn = document.getElementById("remote");
            const label = document.getElementById("locationLabel");
            const locationName = document.getElementById("locationName");
            const addressGroup = document.getElementById("addressGroup");
            const locationLink = document.getElementById("locationLink");
            const attendanceInp = document.getElementById("attendanceMethodInput"); // input hidden

            if (type === "inPerson") {
                inPersonBtn.classList.add("active");
                remoteBtn.classList.remove("active");
                attendanceInp.value = 'حضوري'; // هنا!
                label.innerText = "اسم الموقع";
                locationName.name = "location_name";
                locationName.required = true;
                locationName.placeholder = "ادخل اسم الموقع";
                addressGroup.style.display = "flex";
                locationLink.name = "location_address";
                locationLink.required = true;
            } else {
                remoteBtn.classList.add("active");
                inPersonBtn.classList.remove("active");
                attendanceInp.value = 'عن بعد'; // هنا!
                label.innerText = "رابط التيمز";
                locationName.name = "teams_link";
                locationName.required = true;
                locationName.placeholder = "ادخل رابط التيمز";
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



        /*function showSuccessPopup() {
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


        }*/





        // --- إشعار النجاح من باراميتر ---
        window.onload = function() {
            if (window.location.search.indexOf('success=1') !== -1) {
                document.getElementById('successPopup').classList.add('show');
                setTimeout(() => {
                    window.location.href = 'HomeAddCourse.php';
                }, 1500);
            }
            <?php if (isset($_SESSION['error_edit'])): ?>
                document.getElementById('errorPopup').classList.add('show');
                setTimeout(() => {
                    document.getElementById('errorPopup').classList.remove('show');
                }, 2000);
                <?php unset($_SESSION['error_edit']); ?>
            <?php endif; ?>
        }
















        function toggleDropdown() {
            document.getElementById("dropdownOptions").style.display =
                document.getElementById("dropdownOptions").style.display === "block" ?
                "none" :
                "block";
        }
        // إضافة هذا بعد كود التحقق من checkboxes
        document.querySelectorAll('#dropdownOptions input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', () => {
                const selected = Array.from(document.querySelectorAll('#dropdownOptions input[type="checkbox"]:checked'))
                    .map(cb => cb.value);
                // إضافة هذه السطر لضمان حفظ القيمة في الـinput المخفي
                document.getElementById('changeNoticeInput').value = selected.join(",");
                const label = selected.length > 0 ? selected.join("، ") : "انقر هنا لاختيار التعديل";
                document.getElementById("selectedOptions").innerText = label;
            });
        });


        // إغلاق القائمة إذا تم النقر خارجها
        window.addEventListener('click', function(e) {
            const dropdown = document.querySelector('.custom-multiselect');
            if (!dropdown.contains(e.target)) {
                document.getElementById("dropdownOptions").style.display = "none";
            }
        });













        function editCourse(id) {
            // AJAX لجلب بيانات الدورة
            fetch('get_course.php?id=' + id)
                .then(res => res.json())
                .then(data => {
                    // تعبئة الحقول الأساسية
                    document.querySelector('[name="course_id"]').value = data.id;
                    document.querySelector('[name="course_title"]').value = data.course_title || '';
                    document.querySelector('[name="course_description"]').value = data.course_description || '';
                    document.querySelector('[name="course_objectives"]').value = data.course_objectives ? data.course_objectives.replace(/\\r\\n|\\n|\\r/g, '\n') : '';
                    document.querySelector('[name="course_requirements"]').value = data.course_requirements ? data.course_requirements.replace(/\\r\\n|\\n|\\r/g, '\n') : '';

                    // تعبئة الحقول الأخرى
                    document.querySelector('[name="start_date"]').value = data.start_date || '';
                    document.querySelector('[name="end_date"]').value = data.end_date || '';
                    document.querySelector('[name="start_time"]').value = data.start_time || '';
                    document.querySelector('[name="end_time"]').value = data.end_time || '';
                    document.querySelector('[name="course_duration"]').value = data.course_duration || '';
                    document.querySelector('[name="location_name"]').value = data.location_name || '';
                    document.querySelector('[name="location_address"]').value = data.location_address || '';
                    document.querySelector('[name="teams_link"]').value = data.teams_link || '';
                    document.querySelector('[name="organization"]').value = data.organization || '';
                    document.querySelector('[name="gender"]').value = data.gender || '';
                    document.querySelector('[name="seat_count"]').value = data.seat_count || '';

                    // تعبئة آلية الحضور (وحقلها المخفي)
                    if (data.attendance_method === 'حضوري') {
                        selectAttendance('inPerson');
                    } else if (data.attendance_method === 'عن بعد') {
                        selectAttendance('remote');
                    }
                    document.getElementById('attendanceMethodInput').value = data.attendance_method || '';

                    // إشعار التعديل (Change_Notice)
                    if (data.Change_Notice) {
                        let nots = data.Change_Notice.split(',');
                        document.querySelectorAll('#dropdownOptions input[type="checkbox"]').forEach(cb => {
                            cb.checked = nots.includes(cb.value);
                        });
                        document.getElementById('changeNoticeInput').value = data.Change_Notice;
                        document.getElementById('selectedOptions').innerText = nots.join("، ");
                    } else {
                        // إذا لم يكن هناك إشعار تعديل، فرغ الخيارات
                        document.querySelectorAll('#dropdownOptions input[type="checkbox"]').forEach(cb => cb.checked = false);
                        document.getElementById('changeNoticeInput').value = '';
                        document.getElementById('selectedOptions').innerText = 'انقر هنا لاختيار التعديل';
                    }

                    // تعبئة صورة الدورة (إذا كانت موجودة)
                    if (data.course_image && document.getElementById('courseImage')) {
                        document.getElementById('courseImage').src = data.course_image;
                        document.getElementById('courseImage').style.display = 'block';
                        document.getElementById('placeholderIcon').style.display = 'none';
                    } else if (document.getElementById('courseImage')) {
                        document.getElementById('courseImage').style.display = 'none';
                        document.getElementById('placeholderIcon').style.display = 'block';
                    }

                    // تعبئة ملف الحقيبة التدريبية (اختياري)
                    if (data.training_material && document.getElementById('fileText')) {
                        // استخرج فقط اسم الملف للعرض
                        let fileName = data.training_material.split('/').pop();
                        document.getElementById('fileText').innerHTML = `<b>${fileName}</b>`;
                        document.getElementById('removeFileBtn').style.display = "inline-block";
                    } else if (document.getElementById('fileText')) {
                        document.getElementById('fileText').innerHTML = 'اسحب الملفات هنا أو <u>تصفح الملفات</u>';
                        document.getElementById('removeFileBtn').style.display = "none";
                    }

                    // إذا أردت التمرير للنموذج (اختياري)
                    // document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
                });
        }
    </script>
</body>

</html>