// confirmation.js - Fungsi-fungsi untuk konfirmasi booking

document.addEventListener('DOMContentLoaded', () => {
    // Ambil ID admin dari localStorage
    const adminId = localStorage.getItem('userId');
    const userRole = localStorage.getItem('userRole');
    
    if (!adminId || userRole !== 'admin') {
        showNotification('Akses ditolak. Hanya admin yang dapat mengakses halaman ini.', 'error');
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 2000);
        return;
    }
    
    // Muat daftar booking yang perlu dikonfirmasi
    loadBookingsForConfirmation(adminId);
});

function loadBookingsForConfirmation(adminId) {
    const container = document.getElementById('bookingList');
    
    // Tampilkan pesan loading
    container.innerHTML = '<p class="loading">Memuat data...</p>';

    // Ambil booking berdasarkan admin ID
    fetch(`../../backend/controllers/BookingController.php?action=getByAdmin&id_admin=${adminId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (!data || data.length === 0) {
                container.innerHTML = '<p class="no-results">Tidak ada booking untuk dikonfirmasi.</p>';
                return;
            }

            container.innerHTML = data.map(booking => `
                <div class="booking-card">
                    <h3>${booking.nama_kos}</h3>
                    <p>User: ${booking.user_nama}</p>
                    <p>Tanggal: ${new Date(booking.tanggal).toLocaleDateString('id-ID')}</p>
                    <p class="status-${booking.status}">Status: ${booking.status}</p>
                    <div class="action-buttons">
                        ${booking.status === 'pending' ? `
                            <button class="status-btn confirm-btn" onclick="updateBookingStatus(${booking.id_booking}, 'confirmed')">Konfirmasi</button>
                            <button class="status-btn cancel-btn" onclick="updateBookingStatus(${booking.id_booking}, 'cancelled')">Batalkan</button>
                        ` : ''}
                    </div>
                </div>
            `).join('');
        })
        .catch(error => {
            console.error('Error loading bookings:', error);
            container.innerHTML = '<p class="error">Gagal memuat daftar booking. Silakan coba lagi.</p>';
        });
}

function updateBookingStatus(bookingId, status) {
    const formData = new FormData();
    formData.append('id_booking', bookingId);
    formData.append('status', status);

    fetch('../../backend/controllers/BookingController.php?action=updateStatus', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            // Muat ulang daftar booking
            const adminId = localStorage.getItem('userId');
            loadBookingsForConfirmation(adminId);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat mengupdate status booking', 'error');
    });
}