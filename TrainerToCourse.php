<?php

// --- 1) اتصال بقاعدة البيانات
include 'Connect.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_trainer'])) {
    $selected_trainer_id = intval($_POST['select_user']);
    $course_id = intval($_POST['course_id']);

    // جلب اسم المدرب
    $stmt = $conn->prepare("SELECT name FROM users WHERE id=?");
    $stmt->bind_param("i", $selected_trainer_id);
    $stmt->execute();
    $stmt->bind_result($trainer_name);
    $stmt->fetch();
    $stmt->close();

    // جلب اسم الدورة
    $stmt_course = $conn->prepare("SELECT course_title FROM course_info WHERE id = ?");
    $stmt_course->bind_param("i", $course_id);
    $stmt_course->execute();
    $stmt_course->bind_result($course_title);
    $stmt_course->fetch();
    $stmt_course->close();

    // إضافة صف جديد في courses
    $stmt2 = $conn->prepare("INSERT INTO courses (course_id, course_title, Trainer) VALUES (?, ?, ?)");
    $stmt2->bind_param("iss", $course_id, $course_title, $trainer_name);
    $success = $stmt2->execute();
    $stmt2->close();

    if ($success) {
        $msg = "تم تعيين المدرب وحفظ الدورة بنجاح!";
    } else {
        $msg = "حدث خطأ أثناء الحفظ: " . $conn->error;
    }
}

// جلب رقم الدورة من الرابط (GET)
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$course_title = 'اسم الدورة';
if ($course_id > 0) {
    $stmt = $conn->prepare("SELECT course_title FROM course_info WHERE id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->bind_result($course_title_db);
    if ($stmt->fetch() && $course_title_db) {
        $course_title = $course_title_db;
    }
    $stmt->close();
}

// جزء البحث بالهوية (Ajax)
if (isset($_POST['national_id_search'])) {
    $national_id = trim($_POST['national_id_search']);
    $data = [];

    $sql = "
    SELECT users.id, users.name, users.national_id, users.phone, users.email, workplace.name AS workplace_name
    FROM users
    LEFT JOIN workplace ON users.workplace_id = workplace.id
    WHERE users.national_id = ? AND users.role = 'مدرب'
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $national_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $data['status'] = 'found_trainer';
        $data['user'] = $row;
    } else {
        $sql2 = "SELECT id FROM users WHERE national_id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("s", $national_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        if ($result2->fetch_assoc()) {
            $data['status'] = 'not_trainer';
        } else {
            $data['status'] = 'not_found';
        }
        $stmt2->close();
    }
    $stmt->close();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// جلب جميع المدربين (عرض افتراضي)
$users = [];
$sql = "
SELECT users.id, users.name, users.national_id, users.phone, users.email, workplace.name AS workplace_name
FROM users
LEFT JOIN workplace ON users.workplace_id = workplace.id
WHERE users.role = 'مدرب'
";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>






<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title> تعيين مدرب</title>
    <link rel="stylesheet" href="CSSAdmin/BaseHedar.css">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />



    <style>
        /* ===== تنسيق فلتر البحث ===== */
        #filtersSection {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin: 30px 40px 20px;
            gap: 8px;
        }

        #filtersSection .filter-search {
            position: relative;
            flex: 1;
            max-width: 260px;
        }

        #filtersSection .filter-search input {
            width: 100%;
            padding: 10px 36px 10px 10px;
            border: 1px solid rgba(133, 135, 135, 1);
            border-radius: 12px;
            font-size: 15px;
            direction: rtl;
        }

        #filtersSection .filter-search i {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            color: rgba(133, 135, 135, 1);
        }


        .assign-trainer-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            border: 2px solid rgba(13, 169, 166, 1);
            color: rgba(85, 85, 85, 1);
            border-radius: 12px;
            padding: 9px 17px;
            text-decoration: none;
            font-weight: bold;
            font-size: 15px;
            background-color: rgba(231, 244, 243, 1);
            cursor: pointer;
        }



        .user-list {
            max-width: 800px;
            margin: 40px auto;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .user-row {
            display: flex;
            align-items: center;
            gap: 16px;
            gap: 16px;
            margin-bottom: 18px;
        }

        .user-row:last-child {
            margin-bottom: 0;
        }

        .custom-radio {
            position: relative;
            width: 28px;
            height: 28px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }

        .custom-radio input[type="radio"] {
            opacity: 0;
            width: 28px;
            height: 28px;
            position: absolute;
            right: 0;
            top: 0;
            margin: 0;
            cursor: pointer;
            z-index: 2;
        }

        .custom-radio span {
            display: block;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 2.5px solid #bbb;
            background: #fff;
            transition: border 0.2s, background 0.2s;
            position: relative;
            box-sizing: border-box;
        }

        .custom-radio input[type="radio"]:checked+span {
            border: 2.5px solid #888;
            background: #888;
        }

        .custom-radio input[type="radio"]:checked+span::after {
            display: none;
        }

        .user-box {
            display: grid;
            grid-template-columns: repeat(5, minmax(120px, 1fr));
            background: rgba(246, 246, 246, 1);
            border: 1.5px solid rgba(183, 183, 183, 1);
            border-radius: 16px;
            padding: 11px 10px;
            font-size: 17px;
            transition: border 0.18s;
            cursor: pointer;
            align-items: center;
            min-width: 600px;
            /* لا تقلل عن هذا العرض */
            max-width: 100%;
            width: 100%;
            box-sizing: border-box;
        }

        .user-box.selected,
        .user-row:hover .user-box {
            border-color: #888;
            box-shadow: 0 2px 8px #0001;
        }

        .user-detail {
            color: #333;
            font-size: 16px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: right;
            padding: 0 7px;
            border-left: 1.2px solid #bbb;
        }

        .user-detail:last-child {
            border-left: none;
        }

        /* الغي الرسبونسيف من الأعمدة: */
        @media (max-width: 650px) {
            .user-box {
                overflow-x: auto;
                min-width: 600px;
                font-size: 14px;
            }
        }


        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 3px 13px 3px 9px;
            font-size: 18px;
            font-family: inherit;
            color: #444;
            background: #eaf7f7;
            border: 2px solid #16a5a6;
            border-radius: 13px;
            cursor: pointer;
            font-weight: 500;
            transition: box-shadow 0.18s;
            margin-top: 18px;
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
            /* ليكون في الجهة اليمنى */
            margin: 0 auto 18px auto;
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
                <div class="divider-title" style="color: rgba(8, 105, 130, 1); font-size:35px ;">تعيين مدرب </div>
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


            <form class="user-list" id="usersList" method="POST">
                <div id="filtersSection" style="display: flex; flex-wrap: wrap; justify-content: flex-end; align-items: center; gap: 8px; margin: 30px 40px 20px 40px; flex-direction: row-reverse;">
                    <button type="submit" class="assign-trainer-btn">
                        <i class="far fa-check-circle"></i> تعيين مدرب
                    </button>
                    <div style="position: relative; flex: 1; max-width: 260px;">
                        <i class="fas fa-search" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color:rgba(133, 135, 135, 1) ;"></i>
                        <input type="text" id="searchUser" placeholder="ابحث برقم الهوية" style="width: 100%; padding: 10px 36px 10px 10px; border: 1px solid rgba(133, 135, 135, 1) ; border-radius: 12px; font-size: 15px;" />
                    </div>
                </div>

                <div id="trainersList">
                    <?php foreach ($users as $i => $user): ?>
                        <div class="user-row">
                            <label class="custom-radio">
                                <input type="radio" name="select_user" value="<?= htmlspecialchars($user['id']) ?>" <?= $i === 0 ? 'checked' : '' ?>>
                                <span></span>
                            </label>
                            <label class="user-box">
                                <div class="user-detail"><?= htmlspecialchars($user['name']) ?></div>
                                <div class="user-detail"><?= htmlspecialchars($user['national_id']) ?></div>
                                <div class="user-detail"><?= htmlspecialchars($user['phone']) ?></div>
                                <div class="user-detail"><?= htmlspecialchars($user['email']) ?></div>
                                <div class="user-detail"><?= htmlspecialchars($user['workplace_name']) ?></div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="course_id" value="<?= $course_id ?>">
                <input type="hidden" name="assign_trainer" value="1">
            </form>

            <div class="back-row">
                <button class="btn-back" onclick="window.location.href='AssignTrainer.php'"> <i class="fas fa-chevron-right"></i> رجوع </button>
            </div>


            <div id="searchMsg" style="text-align:center; color:#b70000; font-weight:bold; margin:18px;"></div>



        </main>
    </div>













    <script>
        // تفعيل لينكات القائمة الجانبية
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





        // فلتر البحث بالهوية

        document.getElementById('searchUser').addEventListener('keyup', function() {
            var nationalId = this.value.trim();
            var trainersList = document.getElementById('trainersList'); // الجزء الذي سيتغير
            var searchMsg = document.getElementById('searchMsg');

            if (nationalId.length === 0) {
                window.location.reload();
                return;
            }

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                let res = {};
                try {
                    res = JSON.parse(xhr.responseText);
                } catch (e) {
                    res = {};
                }
                trainersList.innerHTML = ''; // فقط هذا الجزء يتغير
                searchMsg.textContent = '';

                if (res.status === 'found_trainer') {
                    let u = res.user;
                    trainersList.innerHTML =
                        `<div class="user-row">
                    <label class="custom-radio">
                        <input type="radio" name="select_user" value="${u.id}" checked>
                        <span></span>
                    </label>
                    <label class="user-box selected">
                        <div class="user-detail">${u.name}</div>
                        <div class="user-detail">${u.national_id}</div>
                        <div class="user-detail">${u.phone}</div>
                        <div class="user-detail">${u.email}</div>
                        <div class="user-detail">${u.workplace_name}</div>
                    </label>
                </div>`;
                } else if (res.status === 'not_trainer') {
                    searchMsg.textContent = 'هذا المستخدم ليس مدرباً.';
                } else {
                    searchMsg.textContent = 'لا يوجد مستخدم بهذه الهوية.';
                }
            };
            xhr.send("national_id_search=" + encodeURIComponent(nationalId));
        });





        // تمييز المستطيل المختار عند الضغط
        document.querySelectorAll('.user-row').forEach(function(row) {
            const radio = row.querySelector('.custom-radio input[type="radio"]');
            const box = row.querySelector('.user-box');
            box.addEventListener('click', function() {
                radio.checked = true;
                document.querySelectorAll('.user-box').forEach(b => b.classList.remove('selected'));
                box.classList.add('selected');
            });
            radio.addEventListener('change', function() {
                document.querySelectorAll('.user-box').forEach(b => b.classList.remove('selected'));
                if (radio.checked) box.classList.add('selected');
            });
            if (radio.checked) box.classList.add('selected');
        });





        // زر تعيين مدرب يرسل الفورم
        document.getElementById('assignTrainerBtn').onclick = function() {
            document.getElementById('usersList').submit();
        };
    </script>
</body>

</html>