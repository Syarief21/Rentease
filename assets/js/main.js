// main.js - Fungsi-fungsi dasar yang digunakan di seluruh aplikasi

document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.getElementById('navbar-placeholder');
    if (navbar) navbar.style.opacity = "0"; // sembunyikan navbar sementara

    loadHeader();
    loadFooter();
});

// Fungsi untuk memuat header
function loadHeader() {
    fetch('../../views/partials/navbar.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('navbar-placeholder').innerHTML = data;
            updateNavbarBasedOnLoginStatus();

            // tampilkan navbar setelah update selesai
            setTimeout(() => {
                document.getElementById('navbar-placeholder').style.opacity = "1";
            }, 100);
        })
        .catch(error => console.error('Error loading header:', error));
}

// Fungsi untuk memuat footer
function loadFooter() {
    fetch('../../views/partials/footer.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('footer-placeholder').innerHTML = data;
        })
        .catch(error => console.error('Error loading footer:', error));
}

// Fungsi untuk memperbarui navbar berdasarkan status login
function updateNavbarBasedOnLoginStatus() {
    const currentPage = window.location.pathname;

    const navContainer = document.querySelector('.nav-container');
    if (!navContainer) return;

    // Hapus menu sebelumnya jika ada
    const existingMenu = document.querySelector('.nav-menu');
    if (existingMenu) existingMenu.innerHTML = ''; // hanya kosongkan, jangan hapus container

    // Halaman booking, detail & register → hanya logo
    if (
        currentPage.includes("booking.php") ||
        currentPage.includes("detail.php") ||
        currentPage.includes("register.php")
    ) {
        return; // tidak menambahkan menu apapun, logo tetap muncul
    }

    // Halaman login → logo + Beranda
    if (currentPage.includes("login.php")) {
        const userMenu = document.createElement('ul');
        userMenu.className = 'nav-menu';
        userMenu.innerHTML = `
            <li class="nav-item">
                <a href="../user/index.php" class="nav-link">Beranda</a>
            </li>
        `;
        navContainer.appendChild(userMenu);
        return;
    }

    // Halaman lain → navbar normal
    const userRole = localStorage.getItem('userRole');
    const userName = localStorage.getItem('userName');

    const userMenu = document.createElement('ul');
    userMenu.className = 'nav-menu';

    if (userRole) {
        // User sudah login
        if (userRole === 'admin') {
            userMenu.innerHTML = `
                <li class="nav-item">
                    <a href="../admin/dashboard.php" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="logout()">Logout (${userName})</a>
                </li>
            `;
        } else {
            userMenu.innerHTML = `
                <li class="nav-item">
                    <a href="../user/index.php" class="nav-link">Beranda</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="logout()">Logout (${userName})</a>
                </li>
            `;
        }
    } else {
        // User belum login
        userMenu.innerHTML = `
            <li class="nav-item">
                <a href="views/user/index.php" class="nav-link">Beranda</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">Tentang</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">Kontak</a>
            </li>
            <li class="nav-item">
                <a href="views/auth/login.php" class="nav-link">Login</a>
            </li>
        `;
    }

    navContainer.appendChild(userMenu);
}

// Fungsi logout
function logout() {
    fetch('../../backend/controllers/AuthController.php?action=logout', { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                localStorage.clear();
                window.location.href = '../auth/login.php';
            } else {
                alert('Gagal logout, silakan coba lagi');
            }
        })
        .catch(() => {
            localStorage.clear();
            window.location.href = '../auth/login.php';
        });
}
