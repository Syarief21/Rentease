// auth.js - Fungsi-fungsi untuk login dan register

document.addEventListener('DOMContentLoaded', () => {
    // Tangani form login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Tangani form register
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
});

function handleLogin(e) {
    e.preventDefault();
    
    // Ambil data dari form
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    // Validasi form
    if (!email || !password) {
        showNotification('Email dan password harus diisi', 'error');
        return;
    }
    
    // Tampilkan pesan loading
    document.getElementById('loginForm').querySelector('button[type="submit"]').textContent = 'Sedang login...';
    document.getElementById('loginForm').querySelector('button[type="submit"]').disabled = true;
    
    // Kirim data login ke server
    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);
    
    fetch('../../backend/controllers/AuthController.php?action=login', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Kembalikan teks tombol
        document.getElementById('loginForm').querySelector('button[type="submit"]').textContent = 'Login';
        document.getElementById('loginForm').querySelector('button[type="submit"]').disabled = false;
        
        if (data.success) {
            showNotification(data.message, 'success');
            
            // Simpan data pengguna ke localStorage
            localStorage.setItem('userId', data.userId);
            localStorage.setItem('userRole', data.role);
            localStorage.setItem('userName', data.userName);
            
            // Redirect ke halaman tujuan
            setTimeout(() => {
                if(data.role === 'admin') {
                    window.location.href = '../admin/dashboard.php';
                } else {
                    window.location.href = '../user/index.php';
                }
            }, 1500);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Kembalikan teks tombol
        document.getElementById('loginForm').querySelector('button[type="submit"]').textContent = 'Login';
        document.getElementById('loginForm').querySelector('button[type="submit"]').disabled = false;
        showNotification('Terjadi kesalahan saat login. Silakan coba lagi.', 'error');
    });
}

function handleRegister(e) {
    e.preventDefault();
    
    // Ambil data dari form
    const nama = document.getElementById('nama').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Validasi form
    if (!nama || !email || !password || !confirmPassword) {
        showNotification('Semua field harus diisi', 'error');
        return;
    }
    
    if (password !== confirmPassword) {
        showNotification('Password dan konfirmasi password tidak cocok', 'error');
        return;
    }
    
    if (password.length < 6) {
        showNotification('Password minimal 6 karakter', 'error');
        return;
    }
    
    // Kirim data register ke server
    const formData = new FormData();
    formData.append('nama', nama);
    formData.append('email', email);
    formData.append('password', password);
    formData.append('confirm_password', confirmPassword);
    
    fetch('../../backend/controllers/AuthController.php?action=register', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            
            // Redirect ke halaman login setelah 2 detik
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat registrasi', 'error');
    });
}