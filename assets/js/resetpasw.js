
      // ===== RESET PASSWORD HANDLER =====
      document
        .getElementById("resetForm")
        .addEventListener("submit", function (e) {
          e.preventDefault();

          const newPassword = document
            .getElementById("newPassword")
            .value.trim();
          const confirmPassword = document
            .getElementById("confirmPassword")
            .value.trim();

          const savedUser = JSON.parse(localStorage.getItem("userData"));

          if (newPassword !== confirmPassword) {
            alert("Konfirmasi kata sandi tidak cocok!");
            return;
          }

          // Update password di localStorage
          savedUser.password = newPassword;
          localStorage.setItem("userData", JSON.stringify(savedUser));

          alert("Kata sandi berhasil diperbarui! Silakan login kembali.");
          window.location.href = "login.php";
        });

      // ===== TRANSISI LINK (FADE OUT) =====
      document.querySelectorAll("a").forEach((link) => {
        link.addEventListener("click", function (e) {
          const href = this.getAttribute("href");
          if (href && !href.startsWith("#")) {
            e.preventDefault();
            document.body.classList.add("fade-out");
            setTimeout(() => {
              window.location.href = href;
            }, 500);
          }
        });
      });