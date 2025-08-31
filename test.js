// تفعيل زر تسجيل الخروج لفتح المودال
    document.getElementById('logout-link').onclick = function () {
      document.getElementById('logoutModal').classList.add('show');
      document.getElementById('logoutModal').focus();
    };

    // غلق النافذة
    function closeLogoutModal() {
      document.getElementById('logoutModal').classList.remove('show');
    }

    // إعادة التوجيه لتسجيل الخروج
    function logoutRedirect() {
      window.location.href = 'Home.php';
    }

    // إغلاق عند الضغط خارج النافذة
    window.onclick = function (e) {
      const modal = document.getElementById('logoutModal');
      if (modal.classList.contains('show') && e.target === modal) {
        closeLogoutModal();
      }
    };

    // زر الإغلاق (ESC)
   document.addEventListener("DOMContentLoaded", function(){
  setInterval(function(){
    var modal = document.getElementById('logoutModal');
    if(modal && !modal.classList.contains('show')){
      modal.style.display = "none";
      modal.style.pointerEvents = "none";
      modal.style.opacity = "0";
    } else if(modal && modal.classList.contains('show')){
      modal.style.display = "flex";
      modal.style.pointerEvents = "auto";
      modal.style.opacity = "1";
    }
  }, 300);
});

