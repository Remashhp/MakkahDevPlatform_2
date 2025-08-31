<?php

// --- 1) اتصال بقاعدة البيانات
include 'Connect.php';

// جلب معرف الدورة من الرابط
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$course_title = 'اسم الدورة';
if ($course_id > 0) {
    // جلب عنوان الدورة
    $stmt = $conn->prepare("SELECT course_title FROM course_info WHERE id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->bind_result($course_title_db);
    if ($stmt->fetch() && $course_title_db) {
        $course_title = $course_title_db;
    }
    $stmt->close();
}

// معالجة طلب البحث بالهوية عبر AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['national_search'])) {
    $search = trim($_POST['national_search']);
    $stmt = $conn->prepare(
        "SELECT u.national_id, u.name, u.phone
         FROM course_registrations cr
         JOIN users u ON cr.user_id = u.id
         WHERE cr.course_id = ? AND cr.status = 'مقبول' AND u.national_id LIKE ?"
    );
    $like = "%{$search}%";
    $stmt->bind_param("is", $course_id, $like);
    $stmt->execute();
    $stmt->bind_result($national_id, $name, $phone);
    $results = [];
    while ($stmt->fetch()) {
        $results[] = [
            'national_id' => $national_id,
            'name'        => $name,
            'phone'       => $phone,
        ];
    }
    echo json_encode($results);
    exit;
}

// جلب المستخدمين المقبولين لهذه الدورة
$acceptedUsers = [];
if ($course_id > 0) {
    $stmt = $conn->prepare(
        "SELECT u.national_id, u.name, u.phone
         FROM course_registrations cr
         JOIN users u ON cr.user_id = u.id
         WHERE cr.course_id = ? AND cr.status = 'مقبول'"
    );
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->bind_result($national_id, $name, $phone);
    while ($stmt->fetch()) {
        $acceptedUsers[] = [
            'national_id' => $national_id,
            'name'        => $name,
            'phone'       => $phone,
        ];
    }
    $stmt->close();
}
?>



<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title> المستخدمين</title>
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

        .centered-table {
            display: flex;
            justify-content: center;
            margin: 20px 0 40px;
        }

        .users-table {
            border-collapse: collapse;
            width: 90%;
            background: #fff;
            margin-right: 6px;

        }

        .users-table th,
        .users-table td {
            padding: 12px 16px;
            border: 1px solid rgba(24, 24, 24, 1);
            font-size: 14px;
            text-align: center;
        }

        .users-table thead th {
            background-color: #E5F5F3;
            font-weight: 600;

        }

        .users-table tbody tr:hover {
            background-color: #FAFAFA;
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



    <!-- Popup الموحد لجميع الإشعارات -->
    <div id="unifiedPopup" class="popup">
        <span class="info-icon" id="unifiedIcon">✔</span>
        <span id="unifiedMsg"></span>
        <div id="unifiedActions" class="actions" style="display:none">
            <button type="button" class="confirm" onclick="confirmPopupAction()">تأكيد</button>
            <button type="button" class="cancel" onclick="hideUnifiedPopup()">إلغاء</button>
        </div>
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
                <div class="divider-title" style="color: rgba(8, 105, 130, 1); font-size:35px ;">المتدربين</div>
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




            <div class="Course-title" style="color: rgba(85, 85, 85, 1); font-size:30px ; margin-right: 40px; margin-top: 25px;  font-weight: bold;">
                <?= htmlspecialchars($course_title) ?>
            </div>

            <div id="filtersSection" style="display: flex; flex-wrap: wrap; justify-content: flex-end; align-items: center; gap: 8px; margin: 30px 40px 20px 40px; flex-direction: row-reverse;">
                <!-- البحث -->
                <div style="position: relative; flex: 1; max-width: 260px;">
                    <i class="fas fa-search" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color:rgba(133, 135, 135, 1) ;"></i>
                    <input type="text" id="searchUser" placeholder="ابحث برقم الهوية" style="width: 100%; padding: 10px 36px 10px 10px; border: 1px solid rgba(133, 135, 135, 1) ; border-radius: 12px; font-size: 15px;" />
                </div>
            </div>



            <div class="centered-table">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>رقم الهوية</th>
                            <th>الاسم</th>
                            <th>رقم الجوال</th>
                            <th>الاختبار القبلي</th>
                            <th>الاختبار البعدي</th>
                            <th>نسبة الحضور</th>
                        </tr>
                    </thead>
                    <tbody id="usersBody">
                        <?php if (!empty($acceptedUsers)): ?>
                            <?php foreach ($acceptedUsers as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['national_id']) ?></td>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['phone']) ?></td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">لا يوجد مستخدمين مقبولين لهذه الدورة.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="back-row">
                <button class="btn-back" onclick="window.location.href='HomeAddCourse.php'">
                    <i class="fas fa-chevron-right"></i> رجوع
                </button>
            </div>
        </main>
    </div>

    <script src="test.js"></script>
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
            var value = this.value.trim();
            var body = document.getElementById('usersBody');
            if (value === "") {
                // إعادة عرض الكل
                location.reload();
                return;
            }
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                var res = JSON.parse(xhr.responseText || '[]');
                var html = '';
                if (res.length) {
                    res.forEach(function(row) {
                        html += '<tr>' +
                            '<td>' + row.national_id + '</td>' +
                            '<td>' + row.name + '</td>' +
                            '<td>' + row.phone + '</td>' +
                            '<td>-</td>' +
                            '<td>-</td>' +
                            '<td>-</td>' +
                            '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="6">لا توجد نتائج مطابقة.</td></tr>';
                }
                body.innerHTML = html;
            };
            xhr.send('national_search=' + encodeURIComponent(value));
        });













    </script>
</body>

</html>