document.addEventListener("DOMContentLoaded", () => {

  // ==================== DOM ====================
  const modal = document.getElementById("actionModal");
  const modalTitle = document.getElementById("modalTitle");
  const modalMessage = document.getElementById("modalMessage");
  const modalConfirm = document.getElementById("modalConfirm");
  const modalCancel = document.getElementById("modalCancel");

  const popupOverlay = document.getElementById("popupImage");
  const popupImg = document.getElementById("popupImg");
  const popupClose = document.querySelector(".popup-close");

  const bookingList = document.getElementById("bookingList");

  // ==================== EVENT DELEGATION ====================
  if (bookingList) {
    bookingList.addEventListener("click", (e) => {
      const target = e.target;

      // KONFIRMASI
      if (target.classList.contains("btn-confirm")) {
        const id = target.dataset.id;
        if (!id) return;

        showModal(
          "Konfirmasi Booking",
          "Anda yakin ingin menerima booking ini?",
          () => updateBookingStatus(id, "confirmed")
        );
      }

      // TOLAK
      if (target.classList.contains("btn-reject")) {
        const id = target.dataset.id;
        if (!id) return;

        showModal(
          "Tolak Booking",
          "Anda yakin ingin menolak booking ini?",
          () => updateBookingStatus(id, "rejected")
        );
      }

      // POPUP GAMBAR
      if (target.classList.contains("bukti-img") && popupOverlay && popupImg) {
        popupImg.src = target.src;
        popupOverlay.style.display = "flex";
      }
    });
  }

  // ==================== MODAL ====================
  function showModal(title, message, onConfirm) {
    if (!modal || !modalTitle || !modalMessage || !modalConfirm || !modalCancel) return;

    modalTitle.textContent = title;
    modalMessage.textContent = message;
    modal.style.display = "flex";

    modalConfirm.onclick = () => {
      closeModal();
      if (typeof onConfirm === "function") onConfirm();
    };

    modalCancel.onclick = closeModal;
  }

  function closeModal() {
    if (!modal) return;
    modal.style.display = "none";
    modalConfirm.onclick = null;
    modalCancel.onclick = null;
  }

  // ==================== UPDATE STATUS ====================
  async function updateBookingStatus(id, status) {
    try {
      const response = await fetch("../../backend/booking/update_status.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `id=${encodeURIComponent(id)}&status=${encodeURIComponent(status)}`
      });

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.message || "Gagal update status");
      }

      alert("Status booking berhasil diperbarui");
      location.reload();

    } catch (err) {
      console.error(err);
      alert("Terjadi kesalahan saat update booking");
    }
  }

  // ==================== POPUP CLOSE ====================
  if (popupClose && popupOverlay) {
    popupClose.addEventListener("click", () => {
      popupOverlay.style.display = "none";
    });

    popupOverlay.addEventListener("click", (e) => {
      if (e.target === popupOverlay) {
        popupOverlay.style.display = "none";
      }
    });
  }

});
