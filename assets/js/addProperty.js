// addProperty.js - Fungsi-fungsi untuk menambahkan kos baru

document.addEventListener('DOMContentLoaded', () => {
    // Cek apakah pengguna adalah admin
    const userRole = localStorage.getItem('userRole');
    if (userRole !== 'admin') {
        showNotification('Akses ditolak. Hanya admin yang dapat menambahkan kos.', 'error');
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 2000);
        return;
    }
    
    const addPropertyForm = document.getElementById('addPropertyForm');
    
    if (!addPropertyForm) {
        console.error('Form tambah properti tidak ditemukan');
        return;
    }
    
    addPropertyForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        // Ambil data dari form
        const formData = new FormData(addPropertyForm);
        
        // Validasi form
        const namaKos = document.getElementById('namaKos').value;
        const lokasi = document.getElementById('lokasi').value;
        const harga = document.getElementById('harga').value;
        const deskripsi = document.getElementById('deskripsi').value;
        
        if (!namaKos || !lokasi || !harga || !deskripsi) {
            showNotification('Nama kos, lokasi, harga, dan deskripsi harus diisi', 'error');
            return;
        }
        
        if (isNaN(harga) || parseFloat(harga) <= 0) {
            showNotification('Harga harus berupa angka positif', 'error');
            return;
        }
        
        // Tambahkan ID admin dari localStorage
        const adminId = localStorage.getItem('userId');
        formData.append('id_admin', adminId);
        
        // Tampilkan pesan loading
        document.querySelector('button[type="submit"]').textContent = 'Sedang menyimpan...';
        document.querySelector('button[type="submit"]').disabled = true;
        
        // Kirim data ke server
        fetch('../../backend/controllers/PropertyController.php?action=create', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Kembalikan teks tombol
            document.querySelector('button[type="submit"]').textContent = 'Simpan Kos';
            document.querySelector('button[type="submit"]').disabled = false;
            
            if (data.success) {
                showNotification(data.message, 'success');
                // Reset form
                addPropertyForm.reset();
                // Redirect ke dashboard setelah 2 detik
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 2000);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Kembalikan teks tombol
            document.querySelector('button[type="submit"]').textContent = 'Simpan Kos';
            document.querySelector('button[type="submit"]').disabled = false;
            showNotification('Terjadi kesalahan saat menambahkan kos', 'error');
        });
    });
});