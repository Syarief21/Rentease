// ===== EDIT PROFIL ADMIN =====

document.addEventListener('DOMContentLoaded', function() {
    setupProfileEventListeners();
});

function setupProfileEventListeners() {
    const adminProfile = document.querySelector('.admin-profile');
    if (adminProfile) {
        adminProfile.addEventListener('click', function(e) {
            e.preventDefault();
            bukaModalEditProfil();
        });
    }

    const fileInput = document.getElementById('inputFotoProfil');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Preview
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('previewFotoProfil').src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    const btnBatal = document.getElementById('btnBatalProfil');
    if (btnBatal) {
        btnBatal.addEventListener('click', tutupModalEditProfil);
    }

    const form = document.getElementById('formEditProfil');
    if (form) {
        form.addEventListener('submit', handleProfileSubmit);
    }

    const btnOk = document.getElementById('btnOkSukses');
    if (btnOk) {
        btnOk.addEventListener('click', tutupModalSukses);
    }
}

function bukaModalEditProfil() {
    const modal = document.getElementById('modalEditProfil');
    if (!modal) return;
    
    modal.style.display = 'flex';

    // Ambil data dari variabel global yang di-inject oleh PHP
    const user = (typeof serverRenderedAdminProfile !== 'undefined') ? serverRenderedAdminProfile : {};
    
    // Fallback jika user kosong, mungkin perlu fetch API?
    // Tapi dashboard.php sudah menjamin data ini ada.

    document.getElementById('inputNamaProfil').value = user.name || '';
    document.getElementById('inputEmailProfil').value = user.email || '';
    
    const photoUrl = user.profile_picture 
        ? `../../${user.profile_picture}` 
        : '../../assets/img/default.jpg';
        
    document.getElementById('previewFotoProfil').src = photoUrl;
    document.getElementById('inputFotoProfil').value = ''; // Reset file input
}

function tutupModalEditProfil() {
    const modal = document.getElementById('modalEditProfil');
    if (modal) modal.style.display = 'none';
}

function tampilkanModalSukses() {
    const modal = document.getElementById('modalSukses');
    if (modal) modal.style.display = 'flex';
}

function tutupModalSukses() {
    const modal = document.getElementById('modalSukses');
    if (modal) {
        modal.style.display = 'none';
        location.reload(); // Reload untuk melihat perubahan foto/nama
    }
}

async function handleProfileSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    
    // Tambahkan parameter action untuk API
    // (Sebenarnya API users.php memeriksa action via query param atau body?
    // Cek users.php: $action = $_REQUEST['action'] ?? null; Jadi bisa di query string atau body)
    
    // Kita kirim via query string agar aman
    const url = '../../backend/api/users.php?action=update_profile';

    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            tutupModalEditProfil();
            tampilkanModalSukses();
        } else {
            alert('Gagal update profil: ' + result.message);
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        alert('Terjadi kesalahan koneksi.');
    }
}
