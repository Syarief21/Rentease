// ======================== LOAD BOOKING DATA ========================
async function loadBookingData() {
  const res = await fetch("../../backend/controllers/Get_bookingController.php");
  return await res.json();
}


// ======================== UPDATE NOTIF BADGE ========================
function updateNotifBadge() {
  const badge = document.getElementById("notifBadge");
  if (!badge) return;

  const payments = loadBookingData();
  badge.textContent = payments.length;
  badge.style.display = payments.length > 0 ? "inline-block" : "none";
}

// ======================== SHOW BOOKING HISTORY ========================
function showBookingHistory() {
  const overlay = document.getElementById("bookingHistoryOverlay");
  const list = document.getElementById("bookingHistoryList");
  if (!overlay || !list) return;

  const payments = loadBookingData();
  list.innerHTML = "";

  if (payments.length === 0) {
    list.innerHTML = `<p class="empty-history">Tidak ada riwayat booking.</p>`;
    overlay.style.display = "flex";
    return;
  }

  payments.forEach(item => {
    const card = document.createElement("div");
    card.classList.add("history-card");

    // ================= STATUS STYLE =================
    let statusClass = "pending";
    if (item.status === "Ditolak") statusClass = "rejected";
    if (item.status === "Diterima") statusClass = "accepted";
    card.classList.add(statusClass);

    // ================= DATA SAFETY =================
    const namaKos =
      item.namaKos ||
      item.nama_kos ||
      item.kosName ||
      "Nama Kos Tidak Tersedia";

    const alasanHTML =
      item.status === "Ditolak"
        ? `<p class="alasan"><strong>Alasan:</strong> ${item.alasan || "Tidak ada keterangan dari admin."}</p>`
        : "";

    const buktiHTML = item.bukti
      ? `
        <p><strong>Bukti Pembayaran:</strong></p>
        <img src="${item.bukti}" class="bukti-img" alt="Bukti Pembayaran">
      `
      : `<p><strong>Bukti Pembayaran:</strong> -</p>`;

    // ================= TEMPLATE =================
    card.innerHTML = `
      <h4>${namaKos}</h4>
      <p><strong>Nama Pemesan:</strong> ${item.nama || "-"}</p>
      <p><strong>Email:</strong> ${item.email || "-"}</p>
      <p><strong>Status:</strong> <span class="status-text ${statusClass}">${item.status || "-"}</span></p>
      <p><strong>Tanggal Booking:</strong> ${item.tanggal || "-"}</p>
      <p><strong>Metode Pembayaran:</strong> ${item.metode || "-"}</p>
      ${buktiHTML}
      ${alasanHTML}
    `;

    list.appendChild(card);
  });

  overlay.style.display = "flex";
}

// ======================== IMAGE VIEWER ========================
document.addEventListener("click", function (e) {
  if (e.target.classList.contains("bukti-img")) {
    const viewer = document.getElementById("imageViewerOverlay");
    const viewerImg = document.getElementById("imageViewerImg");

    if (!viewer || !viewerImg) return;

    viewerImg.src = e.target.src;
    viewer.style.display = "flex";
  }
});

const closeImageBtn = document.getElementById("closeImageViewer");
if (closeImageBtn) {
  closeImageBtn.addEventListener("click", () => {
    document.getElementById("imageViewerOverlay").style.display = "none";
  });
}

const imageOverlay = document.getElementById("imageViewerOverlay");
if (imageOverlay) {
  imageOverlay.addEventListener("click", e => {
    if (e.target === e.currentTarget) {
      e.currentTarget.style.display = "none";
    }
  });
}

// ======================== CLOSE HISTORY ========================
function closeBookingHistory() {
  const overlay = document.getElementById("bookingHistoryOverlay");
  if (overlay) overlay.style.display = "none";
}

// ======================== REGISTER EVENTS ========================
document.addEventListener("DOMContentLoaded", () => {
  updateNotifBadge();

  const notifBtn = document.getElementById("notifBookingBtn");
  const closeBtn = document.getElementById("closeHistoryBtn");

  if (notifBtn) notifBtn.addEventListener("click", showBookingHistory);
  if (closeBtn) closeBtn.addEventListener("click", closeBookingHistory);
});
