// ====================== Render Kelola Transaksi ======================
function renderKelolaTransaksi() {
  fetch("../../views/admin/kelola_transaksi.php")
    .then((res) => res.text())
    .then((html) => {
      const main = document.getElementById("mainContent");
      main.innerHTML = html;

      function renderStatistikKelolaTransaksi(payments) {
        const totalTransaksi = payments.length;
        const diterima = payments.filter(
          (p) => p.status === "Diterima"
        ).length;
        const ditolak = payments.filter(
          (p) => p.status === "Ditolak"
        ).length;
        const menunggu = payments.filter(
          (p) => p.status === "Menunggu Konfirmasi"
        ).length;

        const totalPendapatan = payments
          .filter((p) => p.status === "Diterima")
          .reduce((sum, p) => sum + Number(p.jumlah || 0), 0);

        document.getElementById("totalTransaksi").textContent =
          totalTransaksi;
        document.getElementById("totalDiterima").textContent = diterima;
        document.getElementById("totalDitolak").textContent = ditolak;
        document.getElementById("totalMenunggu").textContent = menunggu;
        document.getElementById("totalPendapatan").textContent =
          "Rp " + totalPendapatan.toLocaleString("id-ID");

        // Chart Doughnut dengan animasi muter
        const ctx = document
          .getElementById("transaksiChart")
          .getContext("2d");
        if (window.chart) window.chart.destroy();

        window.chart = new Chart(ctx, {
          type: "doughnut",
          data: {
            labels: ["Diterima", "Ditolak", "Menunggu"],
            datasets: [
              {
                data: [diterima, ditolak, menunggu],
                backgroundColor: ["#27ae60", "#e74c3c", "#f1c40f"],
                borderColor: "#fff",
                borderWidth: 2,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "65%",
            rotation: -90, // mulai dari atas
            animation: {
              animateRotate: true, // aktifkan animasi putar
              animateScale: true, // efek membesar dari tengah
              duration: 2000,
              easing: "easeOutCubic",
            },
            plugins: {
              legend: {
                position: "bottom",
              },
            },
          },
        });
      }

      function renderDaftarTransaksi(payments) {
        const container = document.getElementById("transaksiContainer");
        container.innerHTML = "";

        if (payments.length === 0) {
          container.innerHTML =
            "<p style='text-align:center; color:gray'>Belum ada transaksi booking</p>";
          return;
        }

        payments.forEach((p) => {
          const statusClass =
            p.status === "Diterima"
              ? "available"
              : p.status === "Ditolak"
              ? "full"
              : "pending";
          container.innerHTML += `
<div class="property-card ${statusClass}">
  <div class="card-header"><h4>${p.kos}</h4></div>
  <p><strong>Nama:</strong> ${p.nama}</p>
  <p><strong>Email:</strong> ${p.email}</p>
  <p><strong>Jumlah:</strong> Rp ${Number(p.jumlah).toLocaleString(
    "id-ID"
  )}</p>
  <p><strong>Metode:</strong> ${p.metode || "-"}</p>
  <p><strong>Bukti:</strong> ${
    p.bukti
      ? `<img src="${p.bukti}" class="property-image">`
      : "Belum ada"
  }</p>
  <p><strong>Status:</strong> <span class="${statusClass}">${
            p.status
          }</span></p>
</div>
`;
        });
      }

      // Ambil ulang data dari localStorage
      const payments = JSON.parse(localStorage.getItem("payments")) || [];

      // Render statistik & chart berdasarkan data terbaru
      renderStatistikKelolaTransaksi(payments);
      renderDaftarTransaksi(payments);
    });
}