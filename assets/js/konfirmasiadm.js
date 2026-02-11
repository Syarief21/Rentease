// ====================== Render Konfirmasi ======================
function renderKonfirmasi() {
  fetch("../../views/admin/konfirmasi.php")
    .then((res) => res.text())
    .then((html) => {
      const main = document.getElementById("mainContent");
      main.innerHTML = html;

      /* ==========================
         Ambil data booking
      =========================== */
      const payments = JSON.parse(localStorage.getItem("payments")) || [];
      const bookingList = main.querySelector("#bookingList");

      if (!bookingList) return;

      bookingList.innerHTML = "";

      if (payments.length === 0) {
        bookingList.innerHTML = `
        <tr>
          <td colspan="10" style="text-align:center;">Belum ada booking</td>
        </tr>`;
        updateBadgeKonfirmasi();
        return;
      }

      /* ==========================
         Modal Konfirmasi 
      =========================== */
      const modal = document.getElementById("actionModal");
      const modalTitle = document.getElementById("modalTitle");
      const modalMessage = document.getElementById("modalMessage");
      const modalConfirm = document.getElementById("modalConfirm");
      const modalCancel = document.getElementById("modalCancel");

      function showModal(title, message, callback) {
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        modal.style.display = "flex";

        function closeModal() {
          modal.style.display = "none";
        }

        modalConfirm.onclick = () => {
          callback(true);
          closeModal();
        };

        modalCancel.onclick = () => {
          callback(false);
          closeModal();
        };
      }

      /* ==========================
         Modal Form Penolakan
      =========================== */
      const rejectFormModal = document.getElementById("rejectFormModal");
      const rejectEmail = document.getElementById("rejectEmail");
      const rejectReason = document.getElementById("rejectReason");
      const submitRejectForm = document.getElementById("submitRejectForm");
      const cancelRejectForm = document.getElementById("cancelRejectForm");

      let currentRejectIndex = null;

      function openRejectForm(email, index) {
        rejectEmail.value = email;
        rejectReason.value = "";
        currentRejectIndex = index;
        rejectFormModal.style.display = "flex";
      }

      function closeRejectForm() {
        rejectFormModal.style.display = "none";
      }

      cancelRejectForm.addEventListener("click", closeRejectForm);

      submitRejectForm.addEventListener("click", () => {
        if (rejectReason.value.trim() === "") {
          alert("Alasan tidak boleh kosong!");
          return;
        }

        const p = payments[currentRejectIndex];

        showModal(
          "Tolak Booking",
          `Apakah Anda yakin ingin menolak booking dari ${p.nama}?`,
          (ok) => {
            if (ok) {
              p.status = "Ditolak";
              p.alasan = rejectReason.value;
              updatePayments();
            }
          }
        );

        closeRejectForm();
      });

      /* ==========================
         Render Data Booking
      =========================== */
      payments.forEach((p, index) => {
        const row = document.createElement("tr");

        row.innerHTML = `
        <td>${index + 1}</td>
        <td>${p.nama}</td>
        <td>${p.email}</td>
        <td>${p.kos}</td>
        <td>Rp${Number(p.jumlah).toLocaleString()}</td>
        <td>${p.metode || "-"}</td>
        <td>${
          p.bukti
            ? `<img src="${p.bukti}" class="bukti-img" alt="Bukti">`
            : "Tidak ada"
        }</td>
        <td>${p.tanggal || "-"}</td>
        <td>
          <span class="status ${p.status.toLowerCase().replace(" ", "-")}">
            ${p.status}
          </span>
        </td>
        <td class="aksi-cell">
          ${
            p.status === "Menunggu Konfirmasi"
              ? `
            <button class="btn btn-confirm">Konfirmasi</button>
            <button class="btn btn-reject">Tolak</button>`
              : `<button class="btn btn-delete">Hapus Histori</button>`
          }
        </td>
      `;

        bookingList.appendChild(row);

        const confirmBtn = row.querySelector(".btn-confirm");
        const rejectBtn = row.querySelector(".btn-reject");
        const deleteBtn = row.querySelector(".btn-delete");
        const buktiImg = row.querySelector(".bukti-img");

        /* ==========================
           Tombol Konfirmasi
        =========================== */
        if (confirmBtn) {
          confirmBtn.addEventListener("click", () => {
            if (!p.bukti) {
              showModal("Gagal Konfirmasi", "❌ Bukti pembayaran belum diunggah!", () => {});
              return;
            }

            showModal(
              "Konfirmasi Booking",
              `Apakah Anda yakin ingin menerima booking dari ${p.nama}?`,
              (ok) => {
                if (ok) {
                  p.status = "Diterima";
                  updatePayments();
                }
              }
            );
          });
        }

        /* ==========================
           Tombol Buka FORM Tolak
        =========================== */
        if (rejectBtn) {
          rejectBtn.addEventListener("click", () => {
            openRejectForm(p.email, index);
          });
        }

        /* ==========================
           Tombol Hapus
        =========================== */
        if (deleteBtn) {
          deleteBtn.addEventListener("click", () => {
            showModal(
              "Hapus Histori",
              `Yakin ingin menghapus histori booking dari ${p.nama}?`,
              (ok) => {
                if (ok) {
                  payments.splice(index, 1);
                  updatePayments();
                }
              }
            );
          });
        }

        /* ==========================
           Popup bukti gambar
        =========================== */
        if (buktiImg) {
          buktiImg.addEventListener("click", () => {
            const popup = document.getElementById("popupImage");
            const popupImg = document.getElementById("popupImg");

            popupImg.src = p.bukti;
            popup.style.display = "flex";
          });
        }
      });

      /* ==========================
         Close popup bukti
      =========================== */
      const popupOverlay = document.getElementById("popupImage");
      const popupClose = popupOverlay.querySelector(".popup-close");

      popupClose.addEventListener("click", () => {
        popupOverlay.style.display = "none";
      });

      popupOverlay.addEventListener("click", (e) => {
        if (e.target === popupOverlay) popupOverlay.style.display = "none";
      });

      /* ==========================
         Update & Render
      =========================== */
      function updatePayments() {
        localStorage.setItem("payments", JSON.stringify(payments));
        updateBadgeKonfirmasi();
        renderKonfirmasi(); // refresh ulang semua event
      }

      /* ==========================
         Update Badge Setelah Render
      =========================== */
      updateBadgeKonfirmasi();
    })
    .catch((err) => {
      document.getElementById("mainContent").innerHTML =
        `<p>Gagal memuat halaman konfirmasi: ${err}</p>`;
    });
}

/* ==========================
   Fungsi Badge Konfirmasi Booking
========================== */
function updateBadgeKonfirmasi() {
  const payments = JSON.parse(localStorage.getItem("payments")) || [];
  const pending = payments.filter(p => p.status === "Menunggu Konfirmasi").length;

  const badge = document.getElementById("badgeKonfirmasi");

  if (!badge) return;

  if (pending > 0) {
    badge.textContent = pending;
    badge.style.display = "flex";
  } else {
    badge.style.display = "none";
  }
}

/* ==========================
   Auto Update Badge Saat Halaman Dimuat
========================== */
document.addEventListener("DOMContentLoaded", function() {
  // Update badge saat halaman pertama kali dimuat
  updateBadgeKonfirmasi();
  
  // Auto update badge setiap 5 detik untuk memantau perubahan
  setInterval(updateBadgeKonfirmasi, 5000);
});

/* ==========================
   Update Badge Saat localStorage Berubah
========================== */
window.addEventListener("storage", function(e) {
  if (e.key === "payments") {
    updateBadgeKonfirmasi();
  }
});

/* ==========================
   Force Update Badge
   (panggil fungsi ini dari script lain jika perlu)
========================== */
function forceUpdateBadgeKonfirmasi() {
  setTimeout(() => {
    updateBadgeKonfirmasi();
  }, 100);
}