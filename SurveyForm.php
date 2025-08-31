<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
  $host = "localhost";
  $db = "ministry_education_ksa";
  $user = "root";
  $pass = "Remasrr1";
  $conn = new mysqli($host, $user, $pass, $db);
  $conn->set_charset("utf8mb4");
  if ($conn->connect_error) die(json_encode(["status" => "error", "message" => $conn->connect_error]));

  $data = json_decode(file_get_contents('php://input'), true);
  $questions = $data['questions'] ?? [];

  // استقبال الأسئلة المفتوحة من الجافاسكربت مباشرة (القيم التي أدخلها المستخدم)
  $open1 = isset($data['open1']) ? $conn->real_escape_string($data['open1']) : null;
  $open2 = isset($data['open2']) ? $conn->real_escape_string($data['open2']) : null;

  $id = isset($data['id']) ? intval($data['id']) : 0;

  // حذف (delete)
  if (isset($data['delete_id'])) {
    $delete_id = intval($data['delete_id']);
    if ($conn->query("DELETE FROM survey WHERE id = $delete_id")) {
      echo json_encode(["status" => "deleted"]);
    } else {
      echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    exit;
  }

  // تحديث (update)
  if ($id > 0) {
    $setQ = [];
    for ($i = 1; $i <= 15; $i++) {
      $qVal = isset($questions["q$i"]) ? $conn->real_escape_string($questions["q$i"]) : null;
      $setQ[] = "`q$i`=" . ($qVal === null ? "NULL" : "'$qVal'");
    }
    // تحديث الحقول للأسئلة المفتوحة
    $setQ[] = "`open1`=" . ($open1 !== null ? "'$open1'" : "NULL");
    $setQ[] = "`open2`=" . ($open2 !== null ? "'$open2'" : "NULL");

    $sql = "UPDATE survey SET " . implode(',', $setQ) . " WHERE id=$id";
    if ($conn->query($sql)) {
      echo json_encode(["status" => "updated"]);
    } else {
      echo json_encode(["status" => "error", "message" => $conn->error, "sql" => $sql]);
    }
    exit;
  }

  // إدخال جديد (insert)
  $cols = [];
  $vals = [];
  for ($i = 1; $i <= 15; $i++) {
    $cols[] = "`q$i`";
    $vals[] = isset($questions["q$i"]) ? "'" . $conn->real_escape_string($questions["q$i"]) . "'" : "NULL";
  }
  // إضافة الحقول للأسئلة المفتوحة
  $cols[] = "`open1`";
  $vals[] = $open1 !== null ? "'$open1'" : "NULL";
  $cols[] = "`open2`";
  $vals[] = $open2 !== null ? "'$open2'" : "NULL";

  $cols = implode(",", $cols);
  $vals = implode(",", $vals);

  $sql = "INSERT INTO survey ($cols) VALUES ($vals)";
  if ($conn->query($sql)) {
    echo json_encode(["status" => "saved", "id" => $conn->insert_id]);
  } else {
    echo json_encode(["status" => "error", "message" => $conn->error, "sql" => $sql]);
  }
  exit;
}
?>




<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>نموذج الاستبيان</title>
  <link rel="stylesheet" href="CSSAdmin/BaseHedar.css">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />





  <style>
    body,
    .survey-arabic {
      background: #f5f8f9;
    }

    /* توسيط الاستبيان في المنتصف أفقياً مع ظل ومسافة من الأعلى */
    .survey-arabic {
      direction: rtl;
      padding: 36px 24px 60px 24px;
      max-width: 900px;
      width: 100%;
      background-color: white;
      border-radius: 16px;
      box-shadow: 0 2px 18px rgba(0, 0, 0, 0.07);
      margin: 50px auto 40px auto;
      /* auto لتوسيط أفقيًا، و50px من الأعلى */
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .survey-arabic .title-underline {
      border: 0;
      height: 4px;
      width: 220px;
      background: linear-gradient(90deg, #129ca0 60%, #fff 100%);
      margin-top: 0;
      margin-bottom: 40px;
    }

    .group-title {
      font-size: 1.1em;
      margin: 32px 0 12px 0;
      display: flex;
      align-items: center;
      gap: 8px;
      position: relative;
    }

    .group-title input {
      font-size: 1em;
      padding: 3px 9px;
      border-radius: 7px;
      border: 1px solid #bbb;
      width: 300px;
      margin-left: 6px;
    }

    .edit-icon {
      cursor: pointer;
      font-size: 1em;
      color: #888;
      margin-left: 4px;
      transition: color 0.15s;
      user-select: none;
      border: none;
      background: transparent;
      display: flex;
      align-items: center;
    }

    .edit-icon:hover {
      color: #555;
    }

    .delete-icon-btn {
      margin-right: 6px;
      color: #d00;
      border: none;
      background: transparent;
      cursor: pointer;
      font-size: 1em;
      padding: 0 4px;
      display: inline-flex;
      align-items: center;
    }

    .delete-icon-btn:hover {
      color: #b80000;
    }

    .req {
      color: #d00;
      font-size: 0.85em;
      font-weight: normal;
    }

    table {
      width: 100%;
      background: transparent !important;
      border-radius: 8px;
      border-collapse: separate;
      margin-bottom: 8px;
      direction: rtl;
    }

    th,
    td {
      text-align: center;
      padding: 10px 6px;
      font-size: 1em;
    }

    th {
      color: #888;
      font-weight: 500;
    }

    .survey-table th,
    .survey-table td {
      text-align: center;
    }

    .survey-table th.q-text,
    .survey-table td.q-text {
      text-align: right;
      font-weight: 400;
      padding-right: 14px;
      min-width: 180px;
      font-size: 1.07em;
    }

    .survey-table th.radio-header,
    .survey-table td.radio-cell {
      width: 65px;
    }

    .survey-table tbody tr:nth-child(odd) {
      background: #ebeff1;
    }

    .survey-table tbody tr:nth-child(even) {
      background: transparent;
    }

    .survey-arabic input[type="text"],
    .survey-arabic .open-input,
    .survey-arabic .edit-q-input {
      background: transparent !important;
      box-shadow: none;
    }

    .edit-q-input {
      width: 100%;
      font-size: 1em;
      border: 1px solid #ccc;
      border-radius: 6px;
      padding: 4px 8px;
    }

    .add-q-btn-group {
      background: none;
      border: none;
      color: #129ca0;
      font-size: 0.99em;
      cursor: pointer;
      transition: color 0.2s;
      margin: 8px 0 10px 0;
      display: flex;
      align-items: center;
      gap: 3px;
    }

    .add-q-btn-group:hover {
      color: #0c7374;
    }

    .open-q {
      margin-top: 32px;
    }

    .open-input {
      width: 100%;
      min-height: 32px;
      font-size: 1em;
      border: 1px solid #eee;
      border-radius: 8px;
      margin-top: 8px;
      margin-bottom: 10px;
      padding: 10px;
    }

    .note-footer {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      margin: 44px 0 0 0;
      gap: 24px;
    }

    .btns-row {
      display: flex;
      justify-content: space-between;
      margin: 56px 0 0 0;
      gap: 16px;
    }

    .save-btn,
    .send-btn {
      min-width: 120px;
      padding: 10px 0;
      font-size: 1.1em;
      border-radius: 12px;
      border: none;
      background: #129ca0;
      color: #fff;
      cursor: pointer;
      transition: background 0.2s;
    }

    .save-btn {
      background: #129ca0;
    }

    .save-btn:hover {
      background: #0c7374;
    }

    .send-btn {
      background: #0cb5ac;
    }

    .send-btn:hover {
      background: #09978d;
    }

    .popup-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0, 0, 0, 0.23);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }

    .popup-content {
      background: #fff;
      padding: 30px 38px;
      border-radius: 16px;
      box-shadow: 0 2px 16px rgba(0, 0, 0, 0.18);
      min-width: 200px;
      text-align: center;
      font-size: 1.12em;
      position: relative;
      direction: rtl;
    }

    .close-btn {
      position: absolute;
      top: 7px;
      left: 13px;
      font-size: 22px;
      color: #888;
      cursor: pointer;
      font-weight: bold;
    }

    /* Responsive */
    @media (max-width: 900px) {
      .survey-arabic {
        padding: 20px 5px 40px 5px;
        max-width: 98vw;
        margin-top: 20px;
        border-radius: 8px;
      }
    }

    @media (max-width: 700px) {
      .survey-arabic {
        padding: 12px 2px 50px 2px;
        max-width: 99vw;
        margin-top: 10px;
      }

      .popup-content {
        padding: 12px 2px;
        min-width: 70px;
      }

      .btns-row {
        flex-direction: column;
        gap: 18px;
      }

      .group-title input {
        width: 95px;
      }

      .survey-table th.q-text,
      .survey-table td.q-text {
        min-width: 70px;
        font-size: 0.95em;
      }
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
        <div class="divider-title" style="color: rgba(8, 105, 130, 1); font-size:35px ;"> نموذح الاستبيان</div>
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







      <!-- ... رأس الصفحة ... -->





      <div class="survey-arabic">
        <div id="survey-content"></div>
        <!-- Popup -->
        <div id="popup" class="popup-overlay" style="display:none;">
          <div class="popup-content">
            <span class="close-btn" onclick="closePopup()">&times;</span>
            <div id="popup-message"></div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script>
    // كود تمييز الرابط النشط
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

    // متغير لتخزين id الخاص بالسطر في قاعدة البيانات
    let surveyId = 0;

    // بيانات الاستبيان
    let groups = [{
        title: "تقييم المدربة",
        questions: [
          "التمكن من المادة العلمية",
          "القدرة على الحوار وإدارة النقاش",
          "حسن التعامل مع المتدربين",
          "وضوح العرض التقديمي",
          "القدرة على توصيل المعلومة",
        ],
        editing: false
      },
      {
        title: "تقييم المحتوى التدريبي",
        questions: [
          "وضوح أهداف البرنامج",
          "شمولية المادة العلمية",
          "ملاءمة النشاطات بين النظري والعملي",
          "ارتباط النشاطات بواقع العمل",
          "ملاءمة البرنامج لمستويات المتدربين",
        ],
        editing: false
      },
      {
        title: "البيئة التدريبية والخدمات المقدمة",
        questions: [
          "توفر البرنامج التدريبي للتجهيزات والخدمات والبيئة المناسبة التي تتفق إلكترونياً وفق المعايير الخاصة بها",
          "توفر جميع متطلبات الصحة والسلامة العامة في المرافق والتجهيزات",
          "ملاءمة القاعة لعدد المتدربين",
          "الخدمات المقدمة للمتدربين",
          "فريق العمل متعاون ويجيب على الأسئلة والاستفسارات بدقة وبطريقة سهلة",
        ],
        editing: false
      },
    ];
    let openQuestions = [{
        label: "ماهي احتياجاتك التدريبية الأخرى:",
        value: ""
      },
      {
        label: "اقتراحاتكم:",
        value: ""
      },
    ];
    let answers = {};

    function renderSurvey() {
      const survey = document.getElementById('survey-content');
      survey.innerHTML = '';

      groups.forEach((group, groupIdx) => {
        const div = document.createElement('div');
        div.className = "question-group";
        div.innerHTML = `
          <div class="group-title">
            <button class="edit-icon" title="تعديل عنوان وأسئلة المجموعة" onclick="toggleEditGroup(${groupIdx});event.stopPropagation();">
              <i class="fas fa-pen"></i>
            </button>
            ${
              group.editing
                ? `<input type="text" value="${group.title.replace(/"/g, '&quot;')}" oninput="editGroupTitle(${groupIdx},this.value)" />`
                : `<b>${groupIdx + 1}- ${group.title} <span class="req">*</span></b>`
            }
          </div>
          <table class="survey-table">
            <thead>
              <tr>
                <th class="q-text"></th>
                <th class="radio-header">ممتاز</th>
                <th class="radio-header">متوسط</th>
                <th class="radio-header">ضعيف</th>
              </tr>
            </thead>
            <tbody>
              ${group.questions.map((q, qIdx) => `
                <tr>
                  <td class="q-text">
                    ${group.editing ? `
                      <input type="text" value="${q.replace(/"/g, '&quot;')}"
                        oninput="editQuestion('${groupIdx}','${qIdx}',this.value)" class="edit-q-input"/>
                      <button class="delete-icon-btn" onclick="removeQuestion('${groupIdx}','${qIdx}')" title="حذف السؤال">
                        <i class="fas fa-trash"></i>
                      </button>
                    ` : q}
                  </td>
                  <td class="radio-cell">
                    <input type="radio" name="g${groupIdx}_q${qIdx}" value="ممتاز"
                      ${answers[`g${groupIdx}_q${qIdx}`]==="ممتاز"?"checked":""}
                      onchange="setAnswer('${groupIdx}','${qIdx}','ممتاز')">
                  </td>
                  <td class="radio-cell">
                    <input type="radio" name="g${groupIdx}_q${qIdx}" value="متوسط"
                      ${answers[`g${groupIdx}_q${qIdx}`]==="متوسط"?"checked":""}
                      onchange="setAnswer('${groupIdx}','${qIdx}','متوسط')">
                  </td>
                  <td class="radio-cell">
                    <input type="radio" name="g${groupIdx}_q${qIdx}" value="ضعيف"
                      ${answers[`g${groupIdx}_q${qIdx}`]==="ضعيف"?"checked":""}
                      onchange="setAnswer('${groupIdx}','${qIdx}','ضعيف')">
                  </td>
                </tr>
              `).join('')}
            </tbody>
          </table>
          ${
            group.editing
            ? `<button class="add-q-btn-group" onclick="addQuestionToGroup(${groupIdx})"><span>+</span> إضافة سؤال لهذه المجموعة</button>
               <button class="add-q-btn-group" style="color:#888;" onclick="finishEditGroup(${groupIdx})">إنهاء التعديل</button>`
            : ''
          }
        `;
        survey.appendChild(div);
      });

      // الأسئلة المفتوحة
      openQuestions.forEach((item, idx) => {
        const openDiv = document.createElement('div');
        openDiv.className = "open-q";
        openDiv.innerHTML = `
          <div class="group-title">
            <i class="fas fa-pen" style="color:#bbb;font-size:1em; margin-left:8px;"></i>
            <b>${idx + 5}- ${item.label} <span class="req">*</span></b>
          </div>
          <input type="text" placeholder="ادخل إجابتك" value="${item.value.replace(/"/g, '&quot;')}"
            oninput="editOpenQuestion(${idx}, this.value)" class="open-input"/>
        `;
        survey.appendChild(openDiv);
      });

      // ملاحظة وتذييل
      const noteDiv = document.createElement('div');
      noteDiv.className = "note-footer";
      noteDiv.innerHTML = `
        <span>
          <b>7- مع تحيات الجهة المنفذة</b>
          <div>وحدة تطوير الموارد البشرية</div>
        </span>
      `;
      survey.appendChild(noteDiv);

      // أزرار الحفظ والإرسال
      const btnDiv = document.createElement('div');
      btnDiv.className = "btns-row";
      btnDiv.innerHTML = `
        <button class="save-btn" onclick="handleSave()">حفظ</button>
        <button class="send-btn" onclick="handleSubmit()">إرسال!</button>
      `;
      survey.appendChild(btnDiv);
    }

    // تحديثات الأحداث
    function setAnswer(groupIdx, qIdx, val) {
      answers[`g${groupIdx}_q${qIdx}`] = val;
    }

    function editQuestion(groupIdx, qIdx, val) {
      groups[groupIdx].questions[qIdx] = val;
    }

    function editOpenQuestion(idx, val) {
      openQuestions[idx].value = val;
    }

    function addQuestionToGroup(groupIdx) {
      groups[groupIdx].questions.push("سؤال جديد");
      renderSurvey();
      setTimeout(() => {
        const inputs = document.querySelectorAll('.question-group')[groupIdx].querySelectorAll('.edit-q-input');
        if (inputs.length) inputs[inputs.length - 1].focus();
      }, 100);
    }

    function removeQuestion(groupIdx, qIdx) {
      groups[groupIdx].questions.splice(qIdx, 1);
      renderSurvey();
    }

    // دالة الحفظ المعدلة مع id 
    async function handleSave() {
      const allQuestions = {};
      let index = 1;
      groups.forEach(g => {
        g.questions.forEach(q => {
          allQuestions["q" + index] = q;
          index++;
        });
      });

      // اجلب العنوان (label) فقط من كل سؤال مفتوح
      const open1 = openQuestions[0]?.label || "";
      const open2 = openQuestions[1]?.label || "";

      await fetch(window.location.pathname, {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            questions: allQuestions,
            open1: open1,
            open2: open2,
            id: surveyId // هذا هو التعديل الهام (إرسال id)
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === "saved") {
            showPopup("تم حفظ الاستبيان بنجاح!");
            if (data.id) surveyId = data.id; // خزّن الـ id بعد أول insert
          } else if (data.status === "updated") showPopup("تم تحديث الاستبيان!");
          else if (data.status === "deleted") showPopup("تم حذف الاستبيان!");
          else showPopup("حدث خطأ أثناء الحفظ")
        })
        .catch(() => showPopup("حدث خطأ أثناء الحفظ"));
    }

    function handleSubmit() {
      showPopup("تم إرسال الاستبيان للمتدربين بنجاح!");
    }

    function toggleEditGroup(groupIdx) {
      groups[groupIdx].editing = !groups[groupIdx].editing;
      renderSurvey();
      if (groups[groupIdx].editing) {
        setTimeout(() => {
          const input = document.querySelectorAll('.question-group')[groupIdx].querySelector('input[type="text"]');
          if (input) input.focus();
        }, 80);
      }
    }

    function finishEditGroup(groupIdx) {
      groups[groupIdx].editing = false;
      renderSurvey();
    }

    function editGroupTitle(groupIdx, val) {
      groups[groupIdx].title = val;
    }

    function showPopup(msg) {
      document.getElementById('popup-message').innerText = msg;
      document.getElementById('popup').style.display = 'flex';
      setTimeout(closePopup, 1800);
    }

    function closePopup() {
      document.getElementById('popup').style.display = 'none';
    }

    // عند التحميل
    renderSurvey();
    window.setAnswer = setAnswer;
    window.editQuestion = editQuestion;
    window.editOpenQuestion = editOpenQuestion;
    window.addQuestionToGroup = addQuestionToGroup;
    window.removeQuestion = removeQuestion;
    window.handleSave = handleSave;
    window.handleSubmit = handleSubmit;
    window.closePopup = closePopup;
    window.toggleEditGroup = toggleEditGroup;
    window.editGroupTitle = editGroupTitle;
    window.finishEditGroup = finishEditGroup;
  </script>

</body>

</html>