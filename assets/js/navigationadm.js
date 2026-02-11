// ====================== Sidebar Navigation ======================
window.showDashboard = function () {
  // Setup dasar layout dashboard
  document.getElementById("mainContent").innerHTML = `
    <section class="admin-dashboard" id="dashboard">
      <h2>Daftar Kos Saya</h2>
      <div id="adminProperties" class="property-grid">
        <p>Memuat data...</p>
      </div>
    </section>`;

  // Fetch data dari API
  fetch('../../backend/api/properties.php')
    .then(response => response.json())
    .then(result => {
        const grid = document.getElementById('adminProperties');
        if (result.status === 'success' && result.data.length > 0) {
            grid.innerHTML = ''; // Hapus loading
            // Update variabel global
            window.adminProperties = result.data; 
            
            result.data.forEach(p => {
                const isFull = p.available_rooms == 0;
                const card = document.createElement('div');
                card.className = `property-card ${isFull ? 'full' : 'available'}`;
                card.dataset.id = p.id;
                
                card.innerHTML = `
                    <div class="card-header">
                        <h4>${p.name}</h4>
                        <div class="card-menu">
                            <button class="menu-btn" data-id="${p.id}">⋮</button>
                            <div class="menu-dropdown" id="menu-${p.id}">
                                <button class="delete-btn" data-id="${p.id}">Hapus Kos</button>
                                <button class="edit-btn" data-id="${p.id}">Edit Kos</button>
                            </div>
                        </div>
                    </div>
                    <img src="../../${p.image_url || 'assets/img/default.jpg'}" alt="${p.name}" class="property-image">
                    <p><span class="map-icon">📍</span>${p.location}</p>
                    <p>Harga: Rp ${parseInt(p.price).toLocaleString('id-ID')}</p>
                    <p>Deskripsi: ${p.description || '-'}</p>
                    <p>Kamar:
                        <span class="${isFull ? 'kamar-full' : 'kamar-available'}">
                            ${p.available_rooms} / ${p.total_rooms} tersedia
                            ${isFull ? " (Full)" : ""}
                        </span>
                    </p>
                `;
                grid.appendChild(card);
            });

            // Re-attach listeners karena DOM baru dibuat
            if (typeof attachCardActionListeners === 'function') {
                attachCardActionListeners();
            }
        } else {
            grid.innerHTML = '<p>Belum ada kos yang ditambahkan.</p>';
        }
    })
    .catch(err => {
        document.getElementById('adminProperties').innerHTML = `<p>Gagal memuat data: ${err.message}</p>`;
    });
};

document.querySelectorAll(".sidebar-link").forEach((link) => {
  link.addEventListener("click", function (e) {
    e.preventDefault();
    document
      .querySelectorAll(".sidebar-link")
      .forEach((l) => l.classList.remove("active"));
    this.classList.add("active");
    const page = this.getAttribute("data-page");
    if (page === "dashboard") window.showDashboard();
    else if (page === "tambahKos") renderTambahKos();
    else if (page === "pesanMasuk") renderPesanMasuk();
    else if (page === "konfirmasi") renderKonfirmasi();
    else if (page === "kelolaTransaksi") renderKelolaTransaksi();
  });
});

const pageTitles = {
    dashboard: "Dashboard Admin - Rentease",
    tambahKos: "Tambah Kos - Rentease",
    konfirmasi: "Konfirmasi Booking - Rentease",
    kelolaTransaksi: "Kelola Transaksi - Rentease",
    pesanMasuk: "Pesan Masuk - Rentease",
  };

  // EVENT UNTUK UBAH TITLE
  document.querySelectorAll(".sidebar-link").forEach(link => {
    link.addEventListener("click", () => {
      const page = link.dataset.page;
      if (pageTitles[page]) {
        document.title = pageTitles[page];
      }
    });
  });
// ====================== Render Awal ======================
// Tidak perlu panggil renderDashboard() manual jika dashboard.php sudah merender via PHP. 
// Tapi untuk konsistensi SPA, kita biarkan logic ini handle navigasi selanjutnya.