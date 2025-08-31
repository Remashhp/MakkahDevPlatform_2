<?php
session_start();

include 'Connect.php';

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

$course_title = '';
if ($course_id > 0) {
    $sql = "SELECT course_title FROM course_info WHERE id = $course_id";
    $result = $conn->query($sql);
    $course_title = ($result && $row = $result->fetch_assoc()) ? $row['course_title'] : "دورة غير موجودة";
} else {
    $course_title = "لم يتم تحديد دورة";
}

// ترقية من قائمة الاحتياط
if (isset($_POST['promote_reserve']) && isset($_GET['registration_id']) && $course_id > 0) {
    $registration_id = intval($_GET['registration_id']);
    $stmt = $conn->prepare("UPDATE course_registrations SET status = 'مقبول' WHERE id = ?");
    $stmt->bind_param("i", $registration_id);
    $stmt->execute();
    $stmt->close();

    // بعد الترقية، نفذ المفاضلة مرة ثانية لتحديث النتائج في الجلسة
    $stmt = $conn->prepare("SELECT attendance_method FROM course_info WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $course_data = $res->fetch_assoc();
    $stmt->close();

    $limit_accepted = ($course_data['attendance_method'] === 'عن بعد') ? 50 : 30;
    $limit_reserve = ($course_data['attendance_method'] === 'عن بعد') ? 150 : 70;

    $stmt = $conn->prepare("SELECT cr.id AS registration_id, cr.user_id, cr.registration_date, u.name, u.national_id, u.phone, u.email, w.name AS workplace_name
                            FROM course_registrations cr
                            JOIN users u ON cr.user_id = u.id
                            LEFT JOIN workplace w ON u.workplace_id = w.id
                            WHERE cr.course_id = ? AND cr.status IN ('قيد المراجعة', 'احتياط')
                            ORDER BY cr.registration_date ASC");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $accepted = [];
    $reserve = [];
    $rejected = [];

    while ($row = $res->fetch_assoc()) {
        $user_id = $row['user_id'];

        // تحقق من وجود قبول في دورة أخرى
        $check = $conn->prepare("SELECT id FROM course_registrations WHERE user_id = ? AND course_id != ? AND status = 'مقبول' LIMIT 1");
        $check->bind_param("ii", $user_id, $course_id);
        $check->execute();
        $has_other_acceptance = $check->get_result()->num_rows > 0;
        $check->close();

        if ($has_other_acceptance) {
            $row['evaluation_status'] = 'غير مؤهل';
            $rejected[] = $row;
        } elseif (count($accepted) < $limit_accepted) {
            $row['evaluation_status'] = 'مرشح';
            $accepted[] = $row;
        } elseif (count($reserve) < $limit_reserve) {
            $row['evaluation_status'] = 'احتياط';
            $reserve[] = $row;
        } else {
            $row['evaluation_status'] = 'غير مؤهل';
            $rejected[] = $row;
        }
    }

    $stmt->close();

    $_SESSION['evaluation_result'] = array_merge($accepted, $reserve, $rejected);

    $_SESSION['flash_message'] = 'reserveSuccess';
    header("Location: ?course_id=" . $course_id);
    exit;
}

// حذف طلب تسجيل
if (isset($_GET['delete_id']) && $course_id > 0) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM course_registrations WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $success = $stmt->execute();
    $stmt->close();
    unset($_SESSION['evaluation_result']);
    $_SESSION['flash_message'] = $success ? 'approveSuccess' : 'approveError';
    header("Location: ?course_id=" . $course_id);
    exit;
}

// تنفيذ المفاضلة
if (isset($_POST['evaluate_candidates'])) {
    if (!$course_id) {
        die("معرف الدورة غير موجود");
    }

    // معرفة نوع الحضور لتحديد المقاعد
    $stmt = $conn->prepare("SELECT attendance_method FROM course_info WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $course_data = $res->fetch_assoc();
    $stmt->close();

    if (!$course_data) die("الدورة غير موجودة");

    $limit_accepted = ($course_data['attendance_method'] === 'عن بعد') ? 50 : 30;
    $limit_reserve = ($course_data['attendance_method'] === 'عن بعد') ? 150 : 70;

    // حساب المقبولين الحاليين مسبقاً
    $stmt_count = $conn->prepare("SELECT COUNT(*) as count FROM course_registrations WHERE course_id = ? AND status = 'مقبول'");
    $stmt_count->bind_param("i", $course_id);
    $stmt_count->execute();
    $res_count = $stmt_count->get_result();
    $existing_accepted = $res_count->fetch_assoc()['count'] ?? 0;
    $stmt_count->close();

    // المقاعد المتبقية للقبول
    $remaining_accepted = max(0, $limit_accepted - $existing_accepted);

    $stmt = $conn->prepare("SELECT cr.id AS registration_id, cr.user_id, cr.registration_date, u.name, u.national_id, u.phone, u.email, w.name AS workplace_name
                            FROM course_registrations cr
                            JOIN users u ON cr.user_id = u.id
                            LEFT JOIN workplace w ON u.workplace_id = w.id
                        WHERE cr.course_id = ? AND cr.status IN ('قيد المراجعة', 'احتياط')
                            ORDER BY cr.registration_date ASC");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $accepted = [];
    $reserve = [];
    $rejected = [];

    while ($row = $res->fetch_assoc()) {
        $user_id = $row['user_id'];

        // تحقق من وجود قبول في دورة أخرى
        $check = $conn->prepare("SELECT id FROM course_registrations WHERE user_id = ? AND course_id != ? AND status = 'مقبول' LIMIT 1");
        $check->bind_param("ii", $user_id, $course_id);
        $check->execute();
        $has_other_acceptance = $check->get_result()->num_rows > 0;
        $check->close();

        if ($has_other_acceptance) {
            $row['evaluation_status'] = 'غير مؤهل';
            $rejected[] = $row;
        } elseif (count($accepted) < $remaining_accepted) {
            $row['evaluation_status'] = 'مرشح';
            $accepted[] = $row;
        } elseif (count($reserve) < $limit_reserve) {
            $row['evaluation_status'] = 'احتياط';
            $reserve[] = $row;
        } else {
            $row['evaluation_status'] = 'غير مؤهل';
            $rejected[] = $row;
        }
    }

    $stmt->close();

    $_SESSION['evaluation_result'] = array_merge($accepted, $reserve, $rejected);
    $_SESSION['flash_message'] = 'evaluationSuccess';
    header("Location: ?course_id=" . $course_id);
    exit;
}

// اعتماد النتائج
if (isset($_POST['approve']) && isset($_SESSION['evaluation_result'])) {
    $has_error = false;

    foreach ($_SESSION['evaluation_result'] as $row) {
        $registration_id = $row['registration_id'];
        $status = $row['evaluation_status'];
        $final_status = ($status === 'مرشح') ? 'مقبول' : 'مرفوض';

        $stmt = $conn->prepare("UPDATE course_registrations SET status = ? WHERE id = ?");
        if (!$stmt) {
            $has_error = true;
            continue;
        }

        $stmt->bind_param("si", $final_status, $registration_id);
        if (!$stmt->execute()) {
            $has_error = true;
        }
        $stmt->close();
    }

    unset($_SESSION['evaluation_result']);
    $_SESSION['flash_message'] = $has_error ? 'approveError' : 'approveSuccess';
    header("Location: ?course_id=" . $course_id);
    exit;
}

// تحميل جميع الطلبات لعرضها
$registrations = [];
if ($course_id > 0) {
    $sql = "SELECT cr.id AS registration_id, cr.status, u.name, u.national_id, u.phone, u.email, w.name AS workplace_name
            FROM course_registrations cr
            JOIN users u ON cr.user_id = u.id
            LEFT JOIN workplace w ON u.workplace_id = w.id
            WHERE cr.course_id = $course_id";
    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $row['selection'] = '';
            if (isset($_SESSION['evaluation_result'])) {
                foreach ($_SESSION['evaluation_result'] as $eval) {
                    if ($eval['registration_id'] == $row['registration_id']) {
                        $row['selection'] = $eval['evaluation_status'];
                        break;
                    }
                }
            }
            // لو ما عيّنت حالة من المفاضلة، عيّنها من حالة قاعدة البيانات
            if ($row['selection'] === '') {
                if ($row['status'] === 'مقبول') {
                    $row['selection'] = 'مرشح';
                } elseif ($row['status'] === 'احتياط') {
                    $row['selection'] = 'احتياط';
                } elseif ($row['status'] === 'مرفوض') {
                    $row['selection'] = 'غير مؤهل';
                } else {
                    $row['selection'] = 'قيد المراجعة';
                }
            }
            $registrations[] = $row;
        }
    }
}

?>






<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مفاضلة المتدربين</title>
    <link rel="stylesheet" href="CSSAdmin/BaseHedar.css">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />



    <style>
        .button-row {
            margin-top: 30px;
            margin-right: 40px;
            display: flex;
            flex-direction: row;
            gap: 8px;
            align-items: center;
        }

        .custom-btn {
            border: 2px solid rgba(13, 169, 166, 1);
            background: rgba(231, 244, 243, 1);
            color: rgba(74, 73, 73, 1);
            border-radius: 12px;
            font-size: 16px;
            font-weight: bold;
            padding: 6px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s;
            height: 38px;
        }

        .custom-btn i {
            color: rgba(74, 73, 73, 1);
            font-size: 18px;
        }

        .custom-btn:hover {
            background: #e9f7fa;
        }

        .custom-select-wrapper {
            min-width: 110px;
            position: relative;
            display: flex;
            align-items: center;
        }

        .custom-select {
            border: 2px solid rgba(13, 169, 166, 1);
            background: rgba(231, 244, 243, 1);
            color: rgba(74, 73, 73, 1);
            border-radius: 12px;
            font-size: 16px;
            font-weight: bold;
            padding: 6px 5px 6px 16px;
            cursor: pointer;
            outline: none;
            direction: rtl;
            transition: background 0.15s;
            height: 38px;
        }

        .custom-select:focus {
            background: #e9f7fa;
        }

        .custom-select-wrapper select::-ms-expand {
            display: none;
        }

        .custom-select option {
            font-weight: normal;
            color: #333;
            background: #fff;
            border-radius: 0;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: bold;
            padding: 3px 13px 3px 20px;
            font-size: 18px;
            color: #444;
            margin-bottom: 80px;
            background: #eaf7f7;
            border: 2px solid #16a5a6;
            border-radius: 12px;
            cursor: pointer;
            transition: box-shadow 0.18s;
            margin-top: 18px;
            margin-right: 50px;
        }

        .btn-back i {
            font-size: 15px;
            color: #444;
            margin-left: 0;
            margin-right: 0;
        }

        .btn-back:hover {
            box-shadow: 0 2px 8px #0001;
            background: #e5f6f3;
        }

        .back-row {
            width: 90%;
            display: flex;
            justify-content: flex-start;
            margin-top: 98px;
            margin-right: 40px;
        }

        /* --------- تعديلات عرض ووسيط --------- */


        .requests-list {
            direction: rtl;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            margin: 40px 0 0 0;
        }

        .request-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 1050px;
            min-width: 1050px;
            max-width: 98vw;
            background: rgba(246, 246, 246, 1) !important;
            border: 2px solid rgba(183, 183, 183, 1) !important;
            border-radius: 12px !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            padding: 0 15px;
            margin: 12px 0;
            font-size: 16px !important;
            min-height: 52px;
            height: 52px;
            max-height: none;
            box-sizing: border-box;
            transition: box-shadow 0.18s;
        }

        .content {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            font-size: 16px !important;
        }

        .content span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 16px !important;
            padding: 0 10px;
            border-left: 1px solid #e0e0e0;
            box-sizing: border-box;
            height: 36px;
            display: flex;
            align-items: center;
        }

        .content span:last-child {
            border-left: none;
        }

        .name {
            width: 180px;
        }

        .code {
            width: 135px;
        }

        .phone {
            width: 120px;
        }

        .email {
            width: 200px;
        }

        .workplace {
            width: 220px;
        }

        .school {
            width: 120px;
        }





        .actions {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 55px;
        }

        .status {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-accepted {
            background-color: #4caf50;
        }

        .status-rejected {
            background-color: #f44336;
        }

        .delete-btn {
            background: none;
            border: none;
            color: red;
            font-size: 20px;
            cursor: pointer;
        }

        #reserveModal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 650px;
            max-height: 450px;
            background: #f9f9f9;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
            padding: 25px 30px;
            overflow-y: auto;
            z-index: 1050;
            color: rgba(106, 106, 106, 1);
        }

        #reserveModal h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-weight: 700;
            font-size: 24px;
            color: rgba(8, 105, 130, 1);
            border-bottom: 1px solid rgba(85, 85, 85, 1);
            padding-bottom: 8px;
        }

        .reserve-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #ddd;
            font-size: 16px;
        }

        .reserve-item:last-child {
            border-bottom: none;
        }

        .reserve-info {
            display: flex;
            flex-direction: column;
        }

        .reserve-info span {
            margin: 2px 0;
            font-weight: 600;
        }

        .reserve-info small {
            font-weight: 400;
            color: #666;
        }

        .reserve-item button {
            border: 2px solid rgba(13, 169, 166, 1) !important;
            background: rgba(231, 244, 243, 1) !important;
            color: rgba(106, 106, 106, 1);
            border-radius: 12px;
            font-size: 16px;
            padding: 6px 16px;
            font-weight: bold;
            box-shadow: none !important;
        }

        #reserveModal button.close-btn {
            margin-top: 20px;
            background: #dc3545;
            color: white;
            border-radius: 8px;
            border: none;
            padding: 8px 20px;
            font-weight: 700;
            cursor: pointer;
            display: block;
            width: 100%;
        }

        #reserveModal button.close-btn:hover {
            background-color: #a71d2a;
        }

        #modalOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
        }

        /* ======= Popup Styles [من الكود الأول] ======= */
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

        #successPopup,
        #evaluationSuccessPopup,
        #approveSuccessPopup,
        #reserveSuccessPopup {
            background-color: #e6f6fa;
            border: 1px solid #b3d8e8;
            color: #000033;
        }

        #successPopup .info-icon,
        #evaluationSuccessPopup .info-icon,
        #approveSuccessPopup .info-icon,
        #reserveSuccessPopup .info-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            font-size: 24px;
            margin-left: 20px;
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
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            font-size: 50px;
            margin-left: 20px;
            background-color: #d9534f;
            color: white;
            border-radius: 50%;
        }























        /* نافذة تأكيد الحذف الاحترافية */
        #confirmDeletePopup {
            border: 1px solid rgba(110, 110, 110, 1);
            background: #fff;
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


            <div class="Course-title" style="color: rgba(85, 85, 85, 1); font-size:30px ; margin-right: 40px; margin-top: 25px;  font-weight: bold;">
                <?= htmlspecialchars($course_title) ?>
            </div>
            <div class="button-row" dir="rtl">
                <form method="POST" style="display:inline;">
                    <button class="custom-btn" type="submit" name="evaluate_candidates">
                        <i class="fas fa-exchange-alt"></i> مفاضلة
                    </button>
                </form>

                <form method="POST" style="display:inline;">
                    <button class="custom-btn" type="submit" name="approve">
                        <i class="fas fa-check"></i> اعتماد النتائج
                    </button>
                </form>

                <button class="custom-btn" onclick="showReserveModal()"> <i class="fas fa-plus" style="color: rgba(74, 73, 73, 1);"></i> إضافة</button>

                <div class="custom-select-wrapper">
                    <select class="custom-select" id="sortSelect">
                        <option selected disabled value="">ترتيب أبجدي</option>
                        <option value="az">من أ إلى ي</option>
                        <option value="za">من ي إلى أ</option>
                    </select>
                </div>
            </div>


            <!-- popup نجاح المفاضلة -->
            <div id="evaluationSuccessPopup" class="popup">
                <span class="info-icon">✔</span>
                تمت المفاضلة بنجاح!
            </div>

            <!-- popup نجاح اعتماد -->
            <div id="approveSuccessPopup" class="popup">
                <span class="info-icon">✔</span>
                تم اعتماد الترشيح بنجاح!
            </div>

            <!-- popup خطأ اعتماد -->
            <div id="approveErrorPopup" class="popup errorPopup">
                <span class="info-icon">!</span>
                حدث خطأ أثناء الاعتماد!
            </div>

            <!-- popup نجاح احتياط -->
            <div id="reserveSuccessPopup" class="popup">
                <span class="info-icon">✔</span>
                تم إضافة ترشيح مقعد احتياط بنجاح!
            </div>






            <div id="modalOverlay" onclick="closeReserveModal()"></div>
            <div id="reserveModal" style="display:none;">
                <button onclick="closeReserveModal()" style="position:absolute; top:15px; left:20px; font-size:20px; background:none; border:none; cursor:pointer;">✖</button>

                <h3>قائمة الاحتياط</h3>
                <form method="POST" id="reserveForm">
                    <?php
                    if (!empty($_SESSION['evaluation_result'])) {
                        foreach ($_SESSION['evaluation_result'] as $row) {
                            if ($row['evaluation_status'] === 'احتياط') {
                                echo '<div class="reserve-item">';
                                echo '<div class="reserve-info">';
                                echo '<span>' . htmlspecialchars($row['name']) . '</span>';
                                echo '<small>الهوية: ' . htmlspecialchars($row['national_id']) . '</small>';
                                echo '<small>الجوال: ' . htmlspecialchars($row['phone']) . '</small>';
                                echo '<small>البريد: ' . htmlspecialchars($row['email']) . '</small>';
                                echo '<small>جهة العمل: ' . htmlspecialchars($row['workplace_name']) . '</small>';
                                echo '</div>';
                                echo '<button type="submit" name="promote_reserve" value="1" formaction="?course_id=' . $course_id . '&registration_id=' . $row['registration_id'] . '">قبول</button>';

                                echo '</div>';
                            }
                        }
                    } else {
                        echo '<p>لا يوجد بيانات متاحة لعرض الاحتياط حالياً.</p>';
                    }
                    ?>
                </form>
                <button class="close-btn" onclick="closeReserveModal()">إغلاق</button>
            </div>






            <script>
                function showPopup(id) {
                    const popup = document.getElementById(id);
                    if (!popup) return;
                    popup.classList.add('show');
                    setTimeout(() => popup.classList.remove('show'), 1500);
                }


                function showReserveModal() {
                    document.getElementById('modalOverlay').style.display = 'block';
                    document.getElementById('reserveModal').style.display = 'block';
                }

                function closeReserveModal() {
                    document.getElementById('modalOverlay').style.display = 'none';
                    document.getElementById('reserveModal').style.display = 'none';
                }
            </script>




            <!-- قائمة الطلبات -->
            <div class="requests-list" id="requestsList">
                <?php if (!empty($registrations)): ?>
                    <?php foreach ($registrations as $row): ?>
                        <?php
                        // لو بعد المفاضلة و الطلب "احتياط"، ما نعرضه هنا
                        if (isset($_SESSION['evaluation_result']) && ($row['selection'] ?? '') === 'احتياط') {
                            continue;
                        }
                        ?>
                        <div class="request-item">
                            <div class="actions">
                                <?php
                                $statusClass = '';
                                $evaluation = $row['selection'] ?? '';
                                if ($evaluation === 'مرشح') $statusClass = 'status-accepted';
                                elseif ($evaluation === 'احتياط') $statusClass = 'status-reserve';
                                elseif ($evaluation === 'غير مؤهل') $statusClass = 'status-rejected';
                                ?>
                                <?php if ($statusClass): ?>
                                    <span class="status <?= $statusClass ?>"></span>
                                <?php endif; ?>
                                <button class="delete-btn" onclick="confirmDelete(<?= $row['registration_id'] ?>)">✖</button>
                            </div>
                            <div class="content">
                                <span class="name"><?= htmlspecialchars($row['name']) ?></span>
                                <span class="code"><?= htmlspecialchars($row['national_id']) ?></span>
                                <span class="phone"><?= htmlspecialchars($row['phone']) ?></span>
                                <span class="email"><?= htmlspecialchars($row['email']) ?></span>
                                <span class="workplace"><?= htmlspecialchars($row['workplace_name']) ?></span> <!-- جهة العمل -->
                                <span class="school"><?= htmlspecialchars($evaluation) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; font-size: 20px;">لا توجد طلبات تسجيل لهذه الدورة.</p>
                <?php endif; ?>
            </div>



            <!-- نافذة تأكيد حذف طلب تسجيل -->
            <div id="confirmDeletePopup" class="popup">
                <span class="info-icon">؟</span>
                هل أنت متأكد من حذف هذا الطلب؟
                <div class="popup-actions">
                    <button id="confirmDeleteBtn" class="btn-delete">حذف</button>
                    <button id="cancelDeleteBtn" class="btn-edit">إلغاء</button>
                </div>
            </div>










            <div class="back-row">
                <button class="btn-back" onclick="window.location.href='RegistrationRequests.php'"> <i class="fas fa-chevron-right"></i> رجوع </button>
            </div>

    </div>

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

        document.addEventListener('DOMContentLoaded', function() {
            var sortSelect = document.getElementById('sortSelect');
            var requestsList = document.getElementById('requestsList');

            if (!sortSelect || !requestsList) return;

            sortSelect.addEventListener('change', function() {
                var sortOrder = this.value;
                var items = Array.from(requestsList.querySelectorAll('.request-item'));

                // لا تفرز إذا لم يُختر ترتيب
                if (!sortOrder || (sortOrder !== 'az' && sortOrder !== 'za')) return;

                // دالة لجلب الاسم من العنصر
                function getName(el) {
                    var nameSpan = el.querySelector('.name');
                    return nameSpan ? nameSpan.textContent.trim() : '';
                }

                // الترتيب
                items.sort(function(a, b) {
                    var nameA = getName(a);
                    var nameB = getName(b);
                    if (sortOrder === 'az') {
                        return nameA.localeCompare(nameB, 'ar');
                    } else {
                        return nameB.localeCompare(nameA, 'ar');
                    }
                });

                // إعادة بناء القائمة بالترتيب الجديد
                items.forEach(function(item) {
                    requestsList.appendChild(item);
                });
            });
        });
    </script>

    <script>
        // استبدل دالة confirmDelete كاملة بهذا الكود:
        function confirmDelete(id) {
            const courseId = <?= json_encode($course_id) ?>;
            // احفظ الـ id محلياً
            window._deleteRegistrationId = id;
            // إظهار النافذة
            document.getElementById('confirmDeletePopup').classList.add('show');
        }

        // عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            // زر الحذف في النافذة
            document.getElementById('confirmDeleteBtn').onclick = function() {
                const id = window._deleteRegistrationId;
                const courseId = <?= json_encode($course_id) ?>;
                document.getElementById('confirmDeletePopup').classList.remove('show');
                window.location.href = '?course_id=' + courseId + '&delete_id=' + id;
            };
            // زر إلغاء في النافذة
            document.getElementById('cancelDeleteBtn').onclick = function() {
                document.getElementById('confirmDeletePopup').classList.remove('show');
                window._deleteRegistrationId = null;
            };
        });
    </script>



    <?php if (isset($_SESSION['flash_message'])): ?>
        <script>
            window.addEventListener("DOMContentLoaded", function() {
                console.log("Triggering popup: <?= $_SESSION['flash_message'] ?>Popup");
                showPopup("<?= $_SESSION['flash_message'] ?>Popup");
            });
        </script>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>


    </script>

</body>

</html>