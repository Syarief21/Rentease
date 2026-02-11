document.addEventListener("DOMContentLoaded", () => {

  const hubungiBtn = document.getElementById("hubungiAdminBtn");
  const popupOverlay = document.getElementById("popupOverlay");
  const batalBtn = document.getElementById("batalBtn");

  const contactForm = document.getElementById("contactForm");
  const contactFormSection = document.getElementById("contactFormSection");

  const notifModal = document.getElementById("notifModal");
  const notifMessage = document.getElementById("notifMessage");
  const notifCloseBtn = document.getElementById("notifCloseBtn");

  let isLoggedIn = false;
  let authData = null;

  /* ================= CEK LOGIN ================= */
  fetch("../../backend/api/auth.php?action=status")
    .then(res => res.json())
    .then(data => {
      if (data.loggedIn) {
        isLoggedIn = true;
        authData = data.data;
      }
    });

  /* ================= HUBUNGI ADMIN ================= */
  if (hubungiBtn) {
    hubungiBtn.addEventListener("click", () => {
      if (!isLoggedIn) {
        document.getElementById("loginModal").style.display = "flex";
        return;
      }

      document.getElementById("nama").value = authData.userName || '';
      document.getElementById("email").value = authData.email || '';
      popupOverlay.style.display = "block";
    });
  }

  if (batalBtn) {
    batalBtn.addEventListener("click", () => popupOverlay.style.display = "none");
  }

  /* ================= KIRIM PESAN ================= */
  async function handleMessageSend(e) {
    e.preventDefault();

    if (!isLoggedIn) {
      alert("Silakan login terlebih dahulu.");
      return;
    }

    let message = "";
    const formId = e.target.id;

    if (formId === "contactForm") {
      const namaKos = document.getElementById("namaKos").value;
      const pesan = document.getElementById("pesan").value;
      message = `Terkait kos "${namaKos}": ${pesan}`;
    } else {
      message = document.getElementById("pesanKontak").value;
    }

    if (!message.trim()) {
      alert("Pesan tidak boleh kosong");
      return;
    }

    try {
      const response = await fetch(
        "../../backend/api/messages.php?action=reply",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            receiver_id: 1, // ID admin
            message: message
          })
        }
      );

      const result = await response.json();

      if (result.success) {
        notifMessage.textContent = "Pesan berhasil dikirim ke admin.";
        notifModal.style.display = "flex";

        if (formId === "contactForm") {
          popupOverlay.style.display = "none";
          contactForm.reset();
        } else {
          contactFormSection.reset();
        }

      } else {
        throw new Error(result.message || "Gagal mengirim pesan");
      }

    } catch (err) {
      console.error(err);
      alert("Terjadi kesalahan saat mengirim pesan");
    }
  }

  if (contactForm) contactForm.addEventListener("submit", handleMessageSend);
  if (contactFormSection) contactFormSection.addEventListener("submit", handleMessageSend);

  /* ================= NOTIF ================= */
  if (notifCloseBtn) {
    notifCloseBtn.addEventListener("click", () => {
      notifModal.style.display = "none";
    });
  }

});
