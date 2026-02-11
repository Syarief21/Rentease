<?php
// Lakukan koneksi ke database dan ambil data properti
require_once __DIR__ . '/../../backend/config/db.php';
$stmt = $pdo->query("SELECT p.*, u.name as admin_name FROM properties p JOIN users u ON p.admin_id = u.id ORDER BY p.created_at DESC");
$properties = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/jpg" href="../../assets/img/rentease.jpg" />
    <title>Rentease</title>
    <link rel="stylesheet" href="../../assets/css/style.css" />
    <link rel="stylesheet" href="../../assets/css/profil.css" />
    <link rel="stylesheet" href="../../assets/css/hubungi_admin.css" />
    <link rel="stylesheet" href="../../assets/css/kontak.css" />
    <link rel="stylesheet" href="../../assets/css/notification.css" />
  </head>

  <body>
    <div id="navbar-placeholder"></div>
    <!-- Buttons and popups remain the same -->
    <button id="notifBookingBtn" class="notif-booking-icon" title="Riwayat Booking">🔔<span id="notifBadge" class="notif-badge">0</span></button>
    <div id="bookingHistoryOverlay" class="overlay" style="display: none">
      <div class="popup booking-history-box">
        <h2>Riwayat Booking</h2>
        <div id="bookingHistoryList" class="history-list"></div>
        <button id="closeHistoryBtn" class="batal">Tutup</button>
      </div>
    </div>

    <main>
      <section class="properties-section">
        <h2>Daftar Kos Terpopuler</h2>
        <div id="propertyList" class="property-grid">
            <?php if (empty($properties)): ?>
                <p class="no-data">Kos tidak tersedia saat ini.</p>
            <?php else: ?>
                <?php foreach ($properties as $p): ?>
                    <div class="property-card <?= $p['available_rooms'] == 0 ? 'full' : 'available' ?>" data-id="<?= $p['id'] ?>">
                        <img src="../../<?= htmlspecialchars($p['image_url'] ?: 'assets/img/default.jpg') ?>" class="property-image" alt="<?= htmlspecialchars($p['name']) ?>">
                        <h3><?= htmlspecialchars($p['name']) ?></h3>
                        <p><span class="map-icon">📍</span>
                            <a class="location-link" href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($p['location']) ?>" target="_blank">
                                <?= htmlspecialchars($p['location']) ?>
                            </a>
                        </p>
                        <p>Harga: Rp <?= number_format($p['price']) ?></p>
                        <p>Kamar:
                            <span class="<?= $p['available_rooms'] == 0 ? 'kamar-full' : 'kamar-available' ?>">
                                <?= $p['available_rooms'] ?> / <?= $p['total_rooms'] ?>
                                <?= $p['available_rooms'] == 0 ? '(Full)' : '' ?>
                            </span>
                        </p>
                        <button class="booking-btn" <?= $p['available_rooms'] == 0 ? 'disabled' : '' ?>>Booking</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
      </section>

      <!-- Other sections remain the same -->
      <section class="about-section" id="tentang">
        <h2>Tentang Rentease</h2>
        <p>Rentease adalah platform pencarian kos terpercaya yang memudahkan Anda menemukan hunian ideal dengan cepat dan aman.</p>
      </section>
      <section class="features-section">
        <h2>Layanan Unggulan Kami</h2>
        <div class="features-grid">
            <div class="feature-card">🏠<h3>Banyak Pilihan Kos</h3><p>Tersedia beragam kos dari berbagai lokasi dan harga.</p></div>
            <div class="feature-card">💳<h3>Pembayaran Aman</h3><p>Sistem pembayaran online yang aman dan mudah digunakan.</p></div>
            <div class="feature-card">📞<h3>Hubungi Admin</h3><p>Kami siap membantu Anda.</p></div>
        </div>
      </section>
      <section class="contact-section" id="kontak">
        <h2>Hubungi Admin Rentease</h2>
        <p>Isi formulir untuk menghubungi admin.</p>
        <form id="contactFormSection" class="contact-form">
          <label for="namaKontak">Nama Lengkap</label>
          <input type="text" id="namaKontak" name="namaKontak" placeholder="Masukkan nama Anda" required />
          <label for="emailKontak">Email</label>
          <input type="email" id="emailKontak" name="emailKontak" placeholder="Masukkan email Anda" required />
          <label for="pesanKontak">Pesan</label>
          <textarea id="pesanKontak" name="pesanKontak" rows="4" placeholder="Tulis pesan Anda..." required></textarea>
          <button type="submit" class="kirim">Kirim Pesan</button>
        </form>
      </section>
    </main>

    <div id="footer-placeholder"></div>

    <!-- All popups remain the same -->
    <button id="hubungiAdminBtn" title="Hubungi Admin">📞 Hubungi Admin Kos</button>
    <div id="popupOverlay" class="overlay">
      <div class="popup">
        <h2>Hubungi Admin Kos</h2>
        <form id="contactForm">
          <label for="nama">Nama Lengkap</label>
          <input type="text" id="nama" required />
          <label for="email">Email</label>
          <input type="email" id="email" required />
          <label for="namaKos">Nama Kos</label>
          <input type="text" id="namaKos" required />
          <label for="pesan">Pesan</label>
          <textarea id="pesan" rows="4" required></textarea>
          <div class="button-group">
            <button type="submit" class="kirim">Kirim</button>
            <button type="button" class="batal" id="batalBtn">Batal</button>
          </div>
        </form>
      </div>
    </div>
    <div id="loginModal" class="overlay-login">
      <div class="login-popup-box">
        <h3>Login Diperlukan</h3>
        <p>Anda harus login terlebih dahulu agar bisa melanjutkan.</p>
        <button id="loginNowBtn" class="login-yes">Login</button>
        <button id="cancelLoginBtn" class="login-no">Batal</button>
      </div>
    </div>
    <div id="notifModal" class="notif-overlay">
      <div class="notif-box">
        <h3>Pesan Berhasil Dikirim!</h3>
        <p id="notifMessage">Pesan Anda telah terkirim ke admin.</p>
        <button id="notifCloseBtn">Tutup</button>
      </div>
    </div>
    <div id="fullKosModal" class="notif-overlay">
      <div class="notif-box">
        <h3>Mohon Maaf</h3>
        <p id="fullKosMessage">Kamar kos ini sudah penuh.</p>
        <button id="fullKosCloseBtn">Tutup</button>
      </div>
    </div>
    <div id="imageViewerOverlay">
      <div id="imageViewerContent">
        <span id="closeImageViewer">&times;</span>
        <img id="imageViewerImg" src="" alt="Bukti Pembayaran" />
      </div>
    </div>
    <div id="logoutPopupOverlay" class="overlay-logout">
      <div class="logout-popup-box">
        <h3>Konfirmasi Logout</h3>
        <p>Apakah yakin ingin keluar dari akun?</p>
        <div class="logout-buttons">
          <button id="confirmLogoutBtn" class="logout-yes">Ya, Logout</button>
          <button id="cancelLogoutBtn" class="logout-no">Batal</button>
        </div>
      </div>
    </div>
    <div class="edit-profile-overlay" id="editProfileOverlay">
      <div class="edit-profile-popup">
        <button class="close-edit" id="closeEditProfile">&times;</button>
        <h2>Edit Profil</h2>
        <form id="editProfileForm" enctype="multipart/form-data">
          <div class="form-group">
            <label>Foto Profil</label>
            <div class="photo-preview-container"><img id="previewFoto" src="" alt="Preview" style="display: none;"></div>
            <input type="file" id="editFoto" accept="image/*">
          </div>
          <div class="form-group">
            <label for="editUsername">Nama Pengguna</label>
            <input type="text" id="editUsername" required>
          </div>
          <div class="form-group">
            <label for="editEmail">Email</label>
            <input type="email" id="editEmail" required>
          </div>
          <div class="form-buttons">
            <button type="submit" class="btn-simpan">Simpan</button>
            <button type="button" class="btn-tutup" id="closeEditProfile">Tutup</button>
          </div>
        </form>
      </div>
    </div>
    
<!-- Popups and other body elements -->
    
    <script>
        // Hydrate the client-side script with server-rendered data
        const serverRenderedProperties = <?= json_encode($properties) ?>;
    </script>
    <script src="../../assets/js/navbar.js"></script>
    <script src="../../assets/js/property.js"></script>
    <script src="../../assets/js/kontak.js"></script>
    <script src="../../assets/js/modal.js"></script>
    <script src="../../assets/js/logout.js"></script>
    <script src="../../assets/js/notif_booking.js"></script>
  </body>
</html>
