// editProperty.js - Fungsi-fungsi untuk mengedit kos

document.addEventListener('DOMContentLoaded', () => {
    // Cek apakah pengguna adalah admin
    const userRole = localStorage.getItem('userRole');
    if (userRole !== 'admin') {
        showNotification('Akses ditolak. Hanya admin yang dapat mengedit kos.', 'error');
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 2000);
        return;
    }
    
    // Ambil ID properti dari URL
    const urlParams = new URLSearchParams(window.location.search);
    const propertyId = urlParams.get('id');
    
    if (!propertyId) {
        showNotification('ID properti tidak ditemukan', 'error');
        return;
    }
    
    // Muat data properti yang akan diedit
    loadPropertyData(propertyId);
    
    // Tangani submit form edit
    const editPropertyForm = document.getElementById('editPropertyForm');
    if (editPropertyForm) {
        editPropertyForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Ambil data dari form
            const formData = new FormData(editPropertyForm);
            
            // Validasi form
            const namaKos = document.getElementById('namaKosEdit').value;
            const lokasi = document.getElementById('lokasiEdit').value;
            const harga = document.getElementById('hargaEdit').value;
            const deskripsi = document.getElementById('deskripsiEdit').value;
            
            if (!namaKos || !lokasi || !harga || !deskripsi) {
                showNotification('Nama kos, lokasi, harga, dan deskripsi harus diisi', 'error');
                return;
            }
            
            if (isNaN(harga) || parseFloat(harga) <= 0) {
                showNotification('Harga harus berupa angka positif', 'error');
                return;
            }
            
            // Tampilkan pesan loading
            document.querySelector('button[type="submit"]').textContent = 'Sedang menyimpan...';
            document.querySelector('button[type="submit"]').disabled = true;
            
            // Kirim data ke server
            fetch('../../backend/controllers/PropertyController.php?action=update', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Kembalikan teks tombol
                document.querySelector('button[type="submit"]').textContent = 'Update Kos';
                document.querySelector('button[type="submit"]').disabled = false;
                
                if (data.success) {
                    showNotification(data.message, 'success');
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
                document.querySelector('button[type="submit"]').textContent = 'Update Kos';
                document.querySelector('button[type="submit"]').disabled = false;
                showNotification('Terjadi kesalahan saat mengupdate kos', 'error');
            });
        });
    }
});

function loadPropertyData(propertyId) {
    fetch(`../../backend/controllers/PropertyController.php?action=getById&id=${propertyId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(property => {
            if (!property || Object.keys(property).length === 0) {
                showNotification('Data properti tidak ditemukan', 'error');
                return;
            }
            
            // Isi form dengan data properti
            document.getElementById('propertyEditId').value = property.id_property;
            document.getElementById('namaKosEdit').value = property.nama_kos;
            document.getElementById('lokasiEdit').value = property.lokasi;
            document.getElementById('hargaEdit').value = property.harga;
            document.getElementById('deskripsiEdit').value = property.deskripsi;
            
            // Tampilkan foto saat ini
            document.getElementById('currentFoto').src = `../../uploads/property/${property.foto}`;
            document.getElementById('currentFoto').alt = `Foto ${property.nama_kos}`;
            document.getElementById('currentFoto').onerror = function() { this.src = '../../assets/img/default.jpg'; };
        })
        .catch(error => {
            console.error('Error loading property data:', error);
            showNotification('Gagal memuat data kos. Silakan coba lagi.', 'error');
        });
}