
      document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();
  const loadingOverlay = document.getElementById("loadingOverlay");

  // Validasi input dasar
  if (!email || !password) {
    showPopup("Input Tidak Lengkap", "Email dan password harus diisi.");
    return;
  }

  loadingOverlay.classList.add("active");

  // Kirim data ke backend API
  fetch("../../backend/api/auth.php?action=login", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ email, password }),
  })
    .then((response) => response.json())
    .then((data) => {
      loadingOverlay.classList.remove("active");

      if (data.status === "success") {
        // Cek peran pengguna dari respons
        const userRole = data.data.userRole;
        if (userRole === "admin") {
          showPopup("Login Berhasil", "Selamat datang, Admin 👋", () => {
            window.location.href = "../admin/dashboard.php";
          });
        } else {
          showPopup("Login Berhasil", "Selamat datang di Rentease 👋", () => {
            window.location.href = "../user/index.php";
          });
        }
      } else {
        // Tampilkan pesan error dari server
        showPopup("Login Gagal ❌", data.message || "Terjadi kesalahan.");
      }
    })
    .catch((error) => {
      loadingOverlay.classList.remove("active");
      console.error("Error:", error);
      showPopup(
        "Error",
        "Tidak dapat terhubung ke server. Silakan coba lagi nanti."
      );
    });
});

// 🌟 Efek transisi antar halaman
document.querySelectorAll("a").forEach((link) => {
  link.addEventListener("click", function (e) {
    const href = this.getAttribute("href");

    if (href && !href.startsWith("#")) {
      e.preventDefault();
      document.body.classList.add("fade-out");
      setTimeout(() => {
        window.location.href = href;
      }, 700);
    }
  });
});

function showPopup(title, message, callback) {
  const popup = document.getElementById("popupNotif");
  const popTitle = document.getElementById("popupTitle");
  const popMsg = document.getElementById("popupMessage");
  const popBtn = document.getElementById("popupBtn");

  popTitle.innerText = title;
  popMsg.innerHTML = message;

  popup.style.display = "flex";

  popBtn.onclick = () => {
    popup.style.display = "none";
    if (callback) callback();
  };
}