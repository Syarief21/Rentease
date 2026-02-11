// dashboard.js - Fungsi-fungsi untuk dashboard admin

document.addEventListener('DOMContentLoaded', () => {
    // Ambil ID admin dari localStorage (asumsi login sudah disimpan)
    const adminId = localStorage.getItem('userId');
    const userRole = localStorage.getItem('userRole');
    
    if (!adminId || userRole !== 'admin') {
        showNotification('Akses ditolak. Hanya admin yang dapat mengakses halaman ini.', 'error');
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 2000);
        return;
    }

    // Muat daftar properti milik admin
    loadAdminProperties(adminId);
});

function loadAdminProperties(adminId) {
    const container = document.getElementById('adminProperties');
    
    // Tampilkan pesan loading
    container.innerHTML = '<p class="loading">Memuat data...</p>';

    fetch(`../../backend/controllers/PropertyController.php?action=getByAdmin&id_admin=${adminId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (!data || data.length === 0) {
                container.innerHTML = '<p class="no-results">Anda belum memiliki kos terdaftar.</p>';
                return;
            }

            container.innerHTML = data.map(p => `
                <div class="card-admin">
                    <img src="../../uploads/property/${p.foto}" alt="${p.nama_kos}" onerror="this.src='../../assets/img/default.jpg'">
                    <h3>${p.nama_kos}</h3>
                    <p>${formatRupiah(p.harga)}</p>
                    <p>Lokasi: ${p.lokasi}</p>
                    <div class="property-actions">
                        <a href="edit.php?id=${p.id_property}" class="action-btn edit-btn">Edit</a>
                        <a href="#" class="action-btn delete-btn" onclick="hapus(${p.id_property})">Hapus</a>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => {
            console.error('Error loading admin properties:', error);
            container.innerHTML = '<p class="error">Gagal memuat daftar kos. Silakan coba lagi.</p>';
        });
}

function hapus(id) {
    if (confirm("Apakah Anda yakin ingin menghapus properti ini?")) {
        fetch(`../../backend/controllers/PropertyController.php?action=delete&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Muat ulang daftar properti
                    const adminId = localStorage.getItem('userId');
                    loadAdminProperties(adminId);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Terjadi kesalahan saat menghapus kos', 'error');
            });
    }
}