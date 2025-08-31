<?php
// الاتصال بقاعدة البيانات
$conn = new mysqli('localhost', 'root', '12345678', 'ministry_education_ksa');
$conn->set_charset('utf8');

// معالجة تفعيل/تعطيل المستخدم عبر Ajax
if (isset($_POST['toggleUserActive']) && isset($_POST['userId']) && isset($_POST['newStatus'])) {
  $userId = intval($_POST['userId']);
  $newStatus = intval($_POST['newStatus']);
  $update = $conn->prepare("UPDATE users SET is_active=? WHERE id=?");
  $update->bind_param('ii', $newStatus, $userId);
  $update->execute();
  $msg = $newStatus ? "تم تفعيل الحساب" : "تم تعطيل الحساب";
  echo json_encode(["success" => true, "msg" => $msg, "status" => $newStatus]);
  exit;
}

// فلترة برقم الهوية بالـ Ajax
if (isset($_POST['national_search'])) {
  $nid = trim($_POST['national_search']);
  $res = [];
  if ($nid !== '') {
    $stmt = $conn->prepare("SELECT 
      u.id, u.name, u.email, u.role, u.is_active, u.phone, u.created_at,
      u.organization_id, u.workplace_id, u.city_id, u.category_id, u.password,
      o.name AS org_name, w.name AS workplace_name, c.name AS city_name, cat.name AS category_name
      FROM users u
      LEFT JOIN organization o ON u.organization_id = o.id
      LEFT JOIN workplace w ON u.workplace_id = w.id
      LEFT JOIN cities c ON u.city_id = c.id
      LEFT JOIN categories cat ON u.category_id = cat.id
      WHERE u.national_id = ?
      LIMIT 1");
    $stmt->bind_param('s', $nid);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $res[] = $row;
  }
  header('Content-Type: application/json');
  echo json_encode($res);
  exit;
}

// معالجة تعديل المستخدم
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editUserId'])) {
  $id = intval($_POST['editUserId']);
  $name = $_POST['userName'];
  $email = $_POST['userEmail'];
  $phone = $_POST['userPhone'];
  $role = $_POST['userRoleSelect'];
  $work = $_POST['userDept'];
  $org = $_POST['userWork'];
  $city = $_POST['userCity'];
  $type = $_POST['userType'];
  $pass = $_POST['userPass'];

  $update = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, role=?, workplace_id=?, organization_id=?, city_id=?, category_id=?, password=? WHERE id=?");
  $update->bind_param('ssssiiissi', $name, $email, $phone, $role, $work, $org, $city, $type, $pass, $id);
  $update->execute();

  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}


if (isset($_POST['deleteUser']) && isset($_POST['userId'])) {
  $userId = intval($_POST['userId']);
  if ($userId > 0) {
    $delete = $conn->prepare("DELETE FROM users WHERE id=?");
    $delete->bind_param('i', $userId);
    $ok = $delete->execute();
    if ($ok) {
      echo json_encode(["success" => true]);
    } else {
      // أعرض رسالة الخطأ من statement و connection
      $error1 = $delete->error;
      $error2 = $conn->error;
      echo json_encode([
        "success" => false,
        "error" => "stmt: $error1 | conn: $error2"
      ]);
    }
  } else {
    echo json_encode(["success" => false, "error" => "invalid userId"]);
  }
  exit;
}




// --- جلب بيانات المستخدمين ---
$query = "SELECT 
    u.id, u.name, u.email, u.role, u.is_active, u.phone, u.created_at,
    u.organization_id, u.workplace_id, u.city_id, u.category_id,
    u.password,
    o.name AS org_name, w.name AS workplace_name, c.name AS city_name, cat.name AS category_name
  FROM users u
  LEFT JOIN organization o ON u.organization_id = o.id
  LEFT JOIN workplace w ON u.workplace_id = w.id
  LEFT JOIN cities c ON u.city_id = c.id
  LEFT JOIN categories cat ON u.category_id = cat.id
  ORDER BY u.id DESC";
$result = $conn->query($query);

// --- جلب الخيارات للقوائم ---
$orgs = $conn->query("SELECT id, name FROM organization WHERE is_active=1");
$workplaces = $conn->query("SELECT id, name, type FROM workplace WHERE is_active=1");
$cities = $conn->query("SELECT id, name FROM cities WHERE is_active=1");
$categories = $conn->query("SELECT id, name FROM categories WHERE is_active=1");

// تجهيز كل المدارس والأقسام في مصفوفة للجافاسكربت
$all_workplaces = [];
$workplaces_all = $conn->query("SELECT id, name, type FROM workplace WHERE is_active=1");
while ($row = $workplaces_all->fetch_assoc()) {
  $all_workplaces[] = $row;
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

    /* عمود الأيقونات */
    .icon-cell {
      width: 48px;
      text-align: center;
      border-right: none;
      border-left: 1px solid #D9D9D9;
      background: none;
    }

    .icon-cell .fa-edit {
      color: rgb(104, 104, 104);
      font-size: 18px;
      cursor: pointer;
    }

    .users-table th:first-child,
    .users-table td:first-child {
      border-right: none;
    }

    /* نافذة التعديل (سكروول وتوسيط) */
    .modal-backdrop {
      position: fixed;
      top: 0;
      right: 0;
      bottom: 0;
      left: 0;
      background: rgba(0, 0, 0, 0.07);
      z-index: 1000;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      padding-top: 40px;
      direction: rtl;
    }

    .edit-modal {
      background: #fff;
      width: 520px;
      border-radius: 18px;
      box-shadow: 0 4px 18px 0 #0001;
      padding: 0 38px 20px 38px;
      display: flex;
      flex-direction: column;
      position: relative;
      animation: modalIn .24s;
    }

    @keyframes modalIn {
      from {
        transform: translateY(-40px);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .edit-modal-header {
      display: flex;
      flex-direction: row-reverse;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 2px;
      margin-top: 18px;
    }

    .close-modal {
      border: none;
      background: transparent;
      font-size: 26px;
      cursor: pointer;
      color: #444;
      margin-left: 8px;
      margin-right: 0;
    }

    .modal-title {
      font-size: 25px;
      color: #0B6A74;
      font-weight: 600;
      margin-right: 0;
      margin-left: 0;
      margin-top: 10px;
    }

    .edit-modal-divider {
      border: none;
      color: #000;
      border-top: 1px solid #444;
      margin-top: 20px;
      margin-bottom: 20px;
      width: 100%;
    }

    .user-summary-bar {
      display: flex;
      flex-direction: row-reverse;
      align-items: center;
      justify-content: flex-end;
      gap: 22px;
      margin-bottom: 18px;
      margin-top: 4px;
    }

    .user-avatar-bar {
      width: 74px;
      height: 74px;
      border-radius: 50%;
      background: #e1e8ea;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 38px;
      color: #b0b9bf;
    }

    .user-details-right {
      text-align: right;
      min-width: 230px;
    }

    .user-role {
      color: #0B6A74;
      font-size: 17px;
      font-weight: bold;
      margin-bottom: 2px;
    }

    .user-email {
      color: #187186;
      font-size: 15px;
      margin-bottom: 2px;
      margin-top: 2px;
    }

    .user-permission {
      color: #0B6A74;
      font-size: 15px;
      margin-bottom: 19px;
    }

    .user-permission select {
      margin-right: 7px;
      padding: 2px 15px;
      font-size: 15px;
      border-radius: 5px;
      border: 1px solid #b1b1b1;
    }

    /* نموذج التعديل الجديد flex-form-group */
    .edit-form {
      display: flex;
      flex-direction: column;
      gap: 0px;
      margin-top: 32px;
    }

    .flex-form-group {
      display: flex;
      flex-direction: row;
      align-items: center;
      margin-bottom: 18px;
      gap: 18px;
      width: 100%;
    }

    .flex-form-group label {
      width: 130px;
      min-width: 110px;
      max-width: 150px;
      font-size: 15px;
      color: #0B6A74;
      font-weight: bold;
      margin-bottom: 0;
      text-align: right;
      margin-right: 0;
      flex-shrink: 0;
    }

    .flex-form-group input,
    .flex-form-group select {
      flex: 1 1 240px;
      min-width: 0;
      border: 1px solid #b3bfc7;
      border-radius: 8px;
      font-size: 15px;
      padding: 8px 12px;
      background: #fcfdfd;
      transition: border 0.2s;
      direction: rtl;
      text-align: right;

    }

    .flex-form-group input:focus,
    .flex-form-group select:focus {
      outline: none;
      border-color: #099;
    }

    /* أزرار الأسفل */
    .modal-actions {
      display: flex;
      justify-content: flex-start;
      align-items: center;
      margin-top: 20px;
      gap: 12px;
    }

    .btn-outline {
      border: 1.5px solid rgba(128, 128, 128, 1);
      border-radius: 8px;
      color: #666;
      background: rgba(238, 238, 238, 1);
      padding: 7px 15px;
      font-size: 14px;
      cursor: pointer;
      margin-right: 140px;
      transition: background 0.15s, color 0.15s;
    }

    .btn-outline:hover {
      background: #eee;
      color: #000;
    }

    .btn-delete {
      border: 1.5px solid rgba(165, 33, 33, 1);
      border-radius: 8px;
      color: #666;
      background: rgba(244, 231, 231, 1);
      padding: 7px 12px;
      font-size: 14px;
      cursor: pointer;
      transition: background 0.18s, color 0.15s;
    }

    .btn-delete:hover {
      background: #ffd4d8;
      color: #a00;
    }

    .btn-save {
      border: 1.5px solid #0DA9A6;
      border-radius: 8px;
      background: rgba(231, 244, 243, 1);
      color: rgba(8, 105, 130, 1);
      padding: 7px 32px;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.15s, color 0.15s;
    }

    .btn-save:hover {
      background: #e8f6f5;
      color: #097A78;
    }

    @media (max-width: 550px) {
      .edit-modal {
        width: 97vw;
        padding: 0 2vw 20px 2vw;
      }

      .flex-form-group label {
        width: 75px;
        min-width: 60px;
        max-width: 80px;
        font-size: 14px;
      }

      .user-avatar-bar {
        width: 50px;
        height: 50px;
        font-size: 22px;
      }

      .user-details-right {
        min-width: 100px;
      }
    }

    /* نافذة الرسائل المنبثقة */
    #toastModal {
      display: none;
      position: fixed;
      top: 40px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 1100;
      background: #fff;
      color: #086982;
      border: 2px solid #0DA9A6;
      border-radius: 18px;
      padding: 18px 30px;
      font-size: 18px;
      font-weight: 600;
      box-shadow: 0 2px 16px #0994;
      text-align: center;
      min-width: 200px;
      min-height: 30px;
      opacity: 0.98;
    }




    /* Popup الموحد للإشعارات */
    .popup {
      display: none;
      position: fixed;
      top: 15%;
      right: 50%;
      transform: translate(50%, -50%);
      padding: 20px 30px;
      border-radius: 12px;
      width: 420px;
      font-size: 20px;
      z-index: 9999;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
      text-align: right;
      background-color: #e6f6fa;
      border: 1px solid #b3d8e8;
      color: #000033;
      direction: rtl;
    }

    .popup.show {
      display: block;
    }

    .popup .info-icon {
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

    .popup.errorPopup {
      background-color: #fdecea;
      border: 1px solid #f5c2c0;
      color: #a94442;
    }

    .popup .info-icon.error {
      background: #d9534f;
      font-size: 36px;
    }

    .popup .actions {
      margin-top: 18px;
      text-align: left;
    }

    .popup .actions button {
      font-size: 16px;
      border-radius: 8px;
      padding: 7px 22px;
      border: none;
      margin-left: 12px;
      cursor: pointer
    }

    .popup .actions .confirm {
      background: #d9534f;
      color: #fff;
    }

    .popup .actions .cancel {
      background: #fff;
      color: #d9534f;
      border: 1px solid #d9534f;
    }
  </style>

  <script>
    // متغير workplaces (مدارس/أقسام) لجافاسكربت
    var workplaces = <?php echo json_encode($all_workplaces, JSON_UNESCAPED_UNICODE); ?>;
  </script>

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
        <div class="divider-title" style="color: rgba(8, 105, 130, 1); font-size:35px ;">المستخدمين</div>
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
              <th class="icon-cell"></th>
              <th>معرّف المستخدم</th>
              <th>الاسم</th>
              <th>البريد الإلكتروني</th>
              <th>الصلاحية</th>
              <th>حالة الحساب</th>
              <th>تاريخ التسجيل</th>
            </tr>
          </thead>
          <tbody id="usersBody">
            <?php
            if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td class="icon-cell">
        <i class="fas fa-edit" style="cursor:pointer"
          onclick="openEditModal(this)"
          data-id="' . $row['id'] . '"
          data-name="' . htmlspecialchars($row['name']) . '"
          data-email="' . htmlspecialchars($row['email']) . '"
          data-role="' . htmlspecialchars($row['role']) . '"
          data-phone="' . htmlspecialchars($row['phone']) . '"
          data-org="' . $row['organization_id'] . '"
          data-work="' . $row['workplace_id'] . '"
          data-city="' . $row['city_id'] . '"
          data-type="' . $row['category_id'] . '"
          data-password="' . htmlspecialchars($row['password']) . '"
          data-active="' . $row['is_active'] . '"
        ></i>
    </td>';
                echo '<td>U' . str_pad($row['id'], 3, '0', STR_PAD_LEFT) . '</td>';
                echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                echo '<td>' . htmlspecialchars($row['role']) . '</td>';
                echo '<td>' . ($row['is_active'] ? 'نشط' : 'غير نشط') . '</td>';
                echo '<td>' . date('Y-m-d H:i', strtotime($row['created_at'])) . '</td>';
                echo '</tr>';
              }
            } else {
              echo '<tr><td colspan="7">لا يوجد بيانات</td></tr>';
            }
            ?>
          </tbody>

        </table>







      </div>
      <!-- نافذة التعديل -->
      <div class="modal-backdrop" id="editUserModal" style="display: none;">
        <form class="edit-form" method="POST" onsubmit="return validateAndSubmit();">
          <input type="hidden" id="editUserId" name="editUserId" />
          <div class="edit-modal">
            <div class="edit-modal-header">
              <button type="button" class="close-modal" onclick="closeEditModal()">
                <i class="fas fa-times"></i>
              </button>
              <div class="modal-title">تعديل بيانات المستخدم</div>
            </div>
            <hr class="edit-modal-divider" />
            <div class="user-summary-bar">
              <div class="user-details-right">
                <div id="userRoleDisplay" class="user-role">مدرب</div>
                <div class="user-email"></div>
                <div class="user-permission">
                  الصلاحية :
                  <select id="userRoleSelect" name="userRoleSelect">
                    <option value="مدرب">مدرب</option>
                    <option value="متدرب">متدرب</option>
                  </select>
                </div>
              </div>
              <div class="user-avatar-bar">
                <i class="fas fa-user"></i>
              </div>
            </div>
            <div class="flex-form-group">
              <label for="userName">الاسم</label>
              <input type="text" id="userName" name="userName" placeholder="الاسم" />
            </div>
            <div class="flex-form-group">
              <label for="userEmail">البريد الإلكتروني</label>
              <input type="email" id="userEmail" name="userEmail" placeholder="البريد الإلكتروني" />
            </div>
            <div class="flex-form-group">
              <label for="userPhone">رقم الجوال</label>
              <input type="text" id="userPhone" name="userPhone" placeholder="رقم الجوال" />
            </div>
            <div class="flex-form-group">
              <label for="userWork">جهة العمل</label>
              <select id="userWork" name="userWork">
                <option selected disabled value="">اختر جهة العمل</option>
                <?php
                $orgs2 = $conn->query("SELECT id, name FROM organization WHERE is_active=1");
                while ($row = $orgs2->fetch_assoc()): ?>
                  <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="flex-form-group">
              <label id="workplaceLabel" for="userDept">اسم المدرسة / القسم</label>
              <select id="userDept" name="userDept">
                <option selected disabled value="">اختر المدرسة / القسم</option>
                <!-- ستملأ ديناميكياً -->
              </select>
            </div>
            <div class="flex-form-group">
              <label for="userCity">المدينة / المنطقة</label>
              <select id="userCity" name="userCity">
                <option selected disabled value="">اختر المدينة / المنطقة</option>
                <?php
                $cities2 = $conn->query("SELECT id, name FROM cities WHERE is_active=1");
                while ($row = $cities2->fetch_assoc()): ?>
                  <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="flex-form-group">
              <label for="userPass">كلمة المرور</label>
              <input type="text" id="userPass" name="userPass" placeholder="كلمة المرور" />
            </div>
            <div class="flex-form-group">
              <label for="userType">التصنيف</label>
              <select id="userType" name="userType">
                <option selected disabled value="">اختر التصنيف</option>
                <?php
                $categories2 = $conn->query("SELECT id, name FROM categories WHERE is_active=1");
                while ($row = $categories2->fetch_assoc()): ?>
                  <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="modal-actions">
              <button type="submit" class="btn-save">حفظ</button>
              <button type="button" id="toggleActiveBtn" class="btn-outline" onclick="toggleUserActive()">
                <i class="fas fa-ban"></i> تعطيل مستخدم
              </button>
              <button type="button" class="btn-delete" onclick="deleteUserConfirm()"><i class="fas fa-trash"></i> حذف</button>
            </div>
          </div>
        </form>
        <!-- نافذة الرسالة المنبثقة -->
        <div id="toastModal"><span id="toastMsg"></span></div>
      </div>
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

    // تحديث القائمة (المدارس/الأقسام) والليبل حسب جهة العمل
    function fillWorkplaceOptions(selectedType, selectedValue = null) {
      var select = document.getElementById('userDept');
      var label = document.getElementById('workplaceLabel');
      let labelText = 'اسم المدرسة / القسم / الإدارة';

      if (selectedType === 'department') labelText = 'اختر القسم';
      else if (selectedType === 'school') labelText = 'اختر المدرسة';
      else if (selectedType === 'administration') labelText = 'اختر الإدارة';

      select.innerHTML = '<option selected disabled value="">' + labelText + '</option>';

      if (selectedType === 'department' || selectedType === 'school' || selectedType === 'administration') {
        workplaces.forEach(function(item) {
          if (item.type === selectedType) {
            var opt = document.createElement('option');
            opt.value = item.id;
            opt.text = item.name;
            if (selectedValue && selectedValue == item.id) opt.selected = true;
            select.appendChild(opt);
          }
        });
      }

      label.textContent = labelText;
    }


    // عند تغيير جهة العمل
    document.getElementById('userWork').addEventListener('change', function() {
      var workType = '';
      if (this.value == '1') workType = 'department';
      else if (this.value == '2') workType = 'school';
      else if (this.value == '3') workType = 'administration';
      fillWorkplaceOptions(workType);
    });



    // فتح نافذة التعديل وتعبئة البيانات
    function openEditModal(el) {
      document.getElementById('editUserModal').style.display = 'flex';
      document.getElementById('editUserId').value = el.getAttribute('data-id');
      document.getElementById('userName').value = el.getAttribute('data-name');
      document.getElementById('userEmail').value = el.getAttribute('data-email');
      document.getElementById('userPhone').value = el.getAttribute('data-phone');
      document.getElementById('userRoleSelect').value = el.getAttribute('data-role');
      document.getElementById('userRoleDisplay').innerText = el.getAttribute('data-role');
      document.querySelector('.user-email').innerText = el.getAttribute('data-email');
      setSelectByValue('userWork', el.getAttribute('data-org'));
      setSelectByValue('userCity', el.getAttribute('data-city'));
      setSelectByValue('userType', el.getAttribute('data-type'));
      document.getElementById('userPass').value = el.getAttribute('data-password') || '';
      // تحديث القائمة بناءً على جهة العمل
      var workType = '';
      if (document.getElementById('userWork').value == '1') workType = 'department';
      else if (document.getElementById('userWork').value == '2') workType = 'school';
      else if (document.getElementById('userWork').value == '3') workType = 'administration';
      fillWorkplaceOptions(workType, el.getAttribute('data-work'));



      // تحديث بيانات زر تعطيل/تفعيل المستخدم
      let isActive = el.getAttribute('data-active');
      let userId = el.getAttribute('data-id');
      let toggleBtn = document.getElementById('toggleActiveBtn');
      if (isActive == "1") {
        toggleBtn.innerHTML = '<i class="fas fa-ban"></i> تعطيل مستخدم';
        toggleBtn.dataset.active = "1";
      } else {
        toggleBtn.innerHTML = '<i class="fas fa-check"></i> تفعيل مستخدم';
        toggleBtn.dataset.active = "0";
      }
      toggleBtn.dataset.userid = userId;


    }


















    // إغلاق نافذة التعديل
    function closeEditModal() {
      document.getElementById('editUserModal').style.display = 'none';
    }

    // إغلاق عند الضغط خارج المودال
    document.addEventListener('mousedown', function(e) {
      let modal = document.getElementById('editUserModal');
      if (modal && modal.style.display === 'flex' && e.target === modal) closeEditModal();
    });

    // تحديث الصلاحية في العنوان
    document.getElementById('userRoleSelect').addEventListener('change', function() {
      document.getElementById('userRoleDisplay').innerText = this.value;
    });

    function setSelectByValue(selectId, value) {
      var select = document.getElementById(selectId);
      if (!select) return;
      for (var i = 0; i < select.options.length; i++) {
        if (select.options[i].value == value) {
          select.selectedIndex = i;
          break;
        }
      }
    }

    // نافذة الرسالة المنبثقة
    function showToast(msg) {
      var toast = document.getElementById('toastModal');
      document.getElementById('toastMsg').innerText = msg;
      toast.style.display = 'block';
      setTimeout(function() {
        toast.style.display = 'none';
      }, 1800);
    }




    // -------------- Popup احترافي لجميع الرسائل ----------

    let popupActionCallback = null;

    function showUnifiedPopup(msg, type = "success", withConfirm = false, actionCallback = null) {
      const popup = document.getElementById('unifiedPopup');
      const icon = document.getElementById('unifiedIcon');
      const msgSpan = document.getElementById('unifiedMsg');
      const actions = document.getElementById('unifiedActions');

      msgSpan.innerHTML = msg;

      if (type === "success") {
        icon.className = 'info-icon';
        icon.innerHTML = '✔';
        popup.style.background = "#e6f6fa";
        popup.style.color = "#000033";
      } else {
        icon.className = 'info-icon error';
        icon.innerHTML = '!';
        popup.style.background = "#fdecea";
        popup.style.color = "#a94442";
      }

      // عرض زر التأكيد (للحذف مثلاً)
      if (withConfirm) {
        actions.style.display = "block";
        popupActionCallback = actionCallback;
      } else {
        actions.style.display = "none";
        popupActionCallback = null;
      }

      popup.classList.add('show');
      if (!withConfirm) {
        setTimeout(() => {
          popup.classList.remove('show');
        }, 1600);
      }
    }

    function hideUnifiedPopup() {
      document.getElementById('unifiedPopup').classList.remove('show');
      popupActionCallback = null;
    }

    // تعديل: استدعاء callback قبل إخفاء النافذة حتى لا يُمحى مسبقاً
    function confirmPopupAction() {
      if (typeof popupActionCallback === "function") {
        popupActionCallback();
      }
      hideUnifiedPopup();
    }


    // تفعيل/تعطيل المستخدم مع الرسالة الموحدة
    function toggleUserActive() {
      var btn = document.getElementById('toggleActiveBtn');
      var userId = btn.dataset.userid;
      var isActive = btn.dataset.active;
      var newStatus = (isActive == "1") ? 0 : 1;

      var xhr = new XMLHttpRequest();
      xhr.open("POST", "", true);
      xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
      xhr.onload = function() {
        if (xhr.status == 200) {
          try {
            var resp = JSON.parse(xhr.responseText);
            if (resp.success) {
              if (resp.status == 1) {
                btn.innerHTML = '<i class="fas fa-ban"></i> تعطيل مستخدم';
                btn.dataset.active = "1";
              } else {
                btn.innerHTML = '<i class="fas fa-check"></i> تفعيل مستخدم';
                btn.dataset.active = "0";
              }
              showUnifiedPopup(resp.msg, 'success');
              setTimeout(function() {
                closeEditModal();
                location.reload();
              }, 1100);
            }
          } catch (e) {
            showUnifiedPopup("خطأ في معالجة البيانات.", "error");
          }
        } else {
          showUnifiedPopup("حدث خطأ في الاتصال.", "error");
        }
      };
      xhr.send("toggleUserActive=1&userId=" + encodeURIComponent(userId) + "&newStatus=" + encodeURIComponent(newStatus));
    }

    // حذف المستخدم مع تأكيد
    function deleteUserConfirm() {
      showUnifiedPopup('هل أنت متأكد أنك تريد حذف هذا المستخدم بشكل نهائي؟', 'error', true, deleteUserFinal);
    }


    function deleteUserFinal() {
      var userId = document.getElementById('editUserId').value;
      console.log("UserId to delete:", userId);

      if (!userId) return;

      var xhr = new XMLHttpRequest();
      xhr.open("POST", "", true);
      xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
      xhr.onload = function() {
        console.log('Ajax Response:', xhr.responseText); // أضف هذا السطر
        if (xhr.status == 200) {
          try {
            var resp = JSON.parse(xhr.responseText);
            if (resp.success) {
              showUnifiedPopup('تم حذف المستخدم بنجاح!', 'success');
              removeUserRow(userId);
              setTimeout(function() {
                closeEditModal();
              }, 1000);
            } else {
              showUnifiedPopup('حدث خطأ أثناء الحذف!<br>' + (resp.error || ''), 'error');
            }
          } catch (e) {
            showUnifiedPopup('خطأ في معالجة البيانات (js parsing error)', 'error');
            console.log(xhr.responseText);
          }
        } else {
          showUnifiedPopup('حدث خطأ في الاتصال بالسيرفر.', 'error');
        }
      };
      xhr.send("deleteUser=1&userId=" + encodeURIComponent(userId));
    }



    function removeUserRow(userId) {
      var paddedId = "U" + ("00" + parseInt(userId)).slice(-3);
      var rows = document.querySelectorAll('#usersBody tr');
      for (var i = 0; i < rows.length; i++) {
        var cell = rows[i].querySelector('td:nth-child(2)');
        if (cell && (cell.textContent.trim() === paddedId || cell.textContent.replace("U", "").trim() == userId)) {
          rows[i].remove();
          break;
        }
      }
    }





    // فلتر البحث بالهوية
    document.getElementById('searchUser').addEventListener('keyup', function() {
      var value = this.value.trim();
      var body = document.getElementById('usersBody');
      if (value === "") {
        location.reload();
        return;
      }
      var xhr = new XMLHttpRequest();
      xhr.open('POST', '', true);
      xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
      xhr.onload = function() {
        var res = [];
        try {
          res = JSON.parse(xhr.responseText);
        } catch (e) {}
        var html = '';
        if (res.length) {
          res.forEach(function(row) {
            html += '<tr>';
            html += '<td class="icon-cell"><i class="fas fa-edit" style="cursor:pointer" onclick="openEditModal(this)"' +
              ' data-id="' + row.id + '"' +
              ' data-name="' + row.name + '"' +
              ' data-email="' + row.email + '"' +
              ' data-role="' + row.role + '"' +
              ' data-phone="' + row.phone + '"' +
              ' data-org="' + row.organization_id + '"' +
              ' data-work="' + row.workplace_id + '"' +
              ' data-city="' + row.city_id + '"' +
              ' data-type="' + row.category_id + '"' +
              ' data-password="' + row.password + '"' +
              ' data-active="' + row.is_active + '"' +
              '></i></td>';
            html += '<td>U' + row.id.toString().padStart(3, '0') + '</td>';
            html += '<td>' + row.name + '</td>';
            html += '<td>' + row.email + '</td>';
            html += '<td>' + row.role + '</td>';
            html += '<td>' + (row.is_active == '1' ? 'نشط' : 'غير نشط') + '</td>';
            html += '<td>' + row.created_at + '</td>';
            html += '</tr>';
          });
        } else {
          html = '<tr><td colspan="7">لا يوجد بيانات</td></tr>';
        }
        body.innerHTML = html;
      };
      xhr.send("national_search=" + encodeURIComponent(value));
    });















    // فلتر البحث بالهوية 

    /*document.getElementById('searchUser').addEventListener('keyup', function() {
      var value = this.value.trim();
      var body = document.getElementById('usersBody');
      if (value === "") {
        location.reload();
        return;
      }
      var xhr = new XMLHttpRequest();
      xhr.open('POST', '', true);
      xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
      xhr.onload = function() {
        var res = [];
        try {
          res = JSON.parse(xhr.responseText);
        } catch (e) {}
        var html = '';
        if (res.length) {
          res.forEach(function(row) {
            html += '<tr>';
            html += '<td class="icon-cell"><i class="fas fa-edit" style="cursor:pointer" onclick="openEditModal(this)"' +
              ' data-id="' + row.id + '"' +
              ' data-name="' + row.name + '"' +
              ' data-email="' + row.email + '"' +
              ' data-role="' + row.role + '"' +
              ' data-phone="' + row.phone + '"' +
              ' data-org="' + row.organization_id + '"' +
              ' data-work="' + row.workplace_id + '"' +
              ' data-city="' + row.city_id + '"' +
              ' data-type="' + row.category_id + '"' +
              ' data-password="' + row.password + '"' +
              ' data-active="' + row.is_active + '"' +
              '></i></td>';
            html += '<td>U' + row.id.toString().padStart(3, '0') + '</td>';
            html += '<td>' + row.name + '</td>';
            html += '<td>' + row.email + '</td>';
            html += '<td>' + row.role + '</td>';
            html += '<td>' + (row.is_active == '1' ? 'نشط' : 'غير نشط') + '</td>';
            html += '<td>' + row.created_at + '</td>';
            html += '</tr>';
          });
        } else {
          html = '<tr><td colspan="7">لا يوجد بيانات</td></tr>';
        }
        body.innerHTML = html;
      };
      xhr.send("national_search=" + encodeURIComponent(value));
    });*/






    function validateAndSubmit() {
      // يمكنك إضافة فحص بيانات هنا
      return true;
    }
  </script>

</body>

</html>