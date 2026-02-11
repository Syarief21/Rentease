
      const registerForm = document.getElementById("registerForm");
const loadingOverlay = document.getElementById("loadingOverlay");
const successModal = document.getElementById("successModal");
const closeModalBtn = document.getElementById("closeModalBtn");

registerForm.addEventListener("submit", function (e) {
  e.preventDefault();

  const name = document.getElementById("username").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();

  if (!name || !email || !password) {
    alert("Semua field harus diisi!");
    return;
  }

  // Tampilkan overlay loading
  loadingOverlay.classList.add("active");

  // Kirim data ke API registrasi
  fetch("../../backend/api/auth.php?action=register", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ name, email, password, role: 'user' }), // Kirim sebagai 'user'
  })
    .then((response) => response.json())
    .then((data) => {
      loadingOverlay.classList.remove("active");

      if (data.status === "success") {
        // Tampilkan modal popup sukses
        successModal.classList.add("active");
      } else {
        // Tampilkan error dari server
        alert(data.message || "Terjadi kesalahan saat pendaftaran.");
      }
    })
    .catch((error) => {
      loadingOverlay.classList.remove("active");
      console.error("Error:", error);
      alert("Tidak dapat terhubung ke server. Silakan coba lagi nanti.");
    });
});

// Tombol OK menutup modal dan pindah ke login
closeModalBtn.addEventListener("click", () => {
  successModal.classList.remove("active");
  window.location.href = "login.php"; // Arahkan ke .php bukan .html
});

// Tombol Google (placeholder)
document.getElementById("googleLoginBtn").addEventListener("click", () => {
  alert("Fitur login dengan Google belum diaktifkan.");
});

// Fade out untuk semua link
document.querySelectorAll("a").forEach((link) => {
  link.addEventListener("click", function (e) {
    const href = this.getAttribute("href");
    if (href && !href.startsWith("#")) {
      e.preventDefault();
      document.body.classList.add("fade-out");
      setTimeout(() => {
        window.location.href = href.replace('.html', '.php'); // Pastikan link mengarah ke .php
      }, 700);
    }
  });
});