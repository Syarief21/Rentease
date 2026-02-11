// ================= LOGOUT JS =================
document.addEventListener("DOMContentLoaded", () => {
  // Cek jika tombol logout ada di DOM (untuk admin dashboard)
  const logoutButtonAdmin = document.querySelector('.logout-btn');
  if (logoutButtonAdmin) {
    logoutButtonAdmin.addEventListener('click', () => {
      // Langsung logout tanpa konfirmasi untuk admin dashboard
      performLogout();
    });
  }

  // Cek jika tombol logout ada di navbar user
  const logoutBtnUser = document.getElementById("logoutBtn");
  const logoutPopup = document.getElementById("logoutPopupOverlay");
  const confirmLogoutBtn = document.getElementById("confirmLogoutBtn");
  const cancelLogoutBtn = document.getElementById("cancelLogoutBtn");

  if (logoutBtnUser) {
    logoutBtnUser.addEventListener("click", () => {
      logoutPopup.style.display = "flex";
    });

    cancelLogoutBtn.addEventListener("click", () => {
      logoutPopup.style.display = "none";
    });

    confirmLogoutBtn.addEventListener("click", () => {
      performLogout();
    });
  }
});

function performLogout() {
  // Panggil API untuk menghancurkan sesi di server
  fetch("../../backend/api/auth.php?action=logout", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        console.log("Server session destroyed.");
      }
    })
    .catch(error => {
      console.error("Logout request failed:", error);
    })
    .finally(() => {
      // Selalu redirect ke halaman login setelah mencoba logout
      window.location.href = "../../views/auth/login.php";
    });
}

