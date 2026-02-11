<?php
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/session.php';

Session::start();

$isLoggedIn = Session::isLoggedIn();
$userData = null;
if ($isLoggedIn) {
    $userId = Session::get('user_id');
    // Assuming 'users' table has 'id', 'name', 'email', 'profile_picture'
    $stmt = $pdo->prepare("SELECT name, email, profile_picture FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();
}


$property_id = isset($_GET['id']) ? $_GET['id'] : null;
$property = null;

if ($property_id) {
    $stmt = $pdo->prepare("SELECT p.* FROM properties p WHERE p.id = ?");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch();
}

$page_title = $property ? htmlspecialchars($property['name']) . " - Rentease" : "Properti Tidak Ditemukan";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/jpg" href="../../assets/img/rentease.jpg" />
  <title><?= $page_title ?></title>
  <link rel="stylesheet" href="../../assets/css/style.css" />
  <link rel="stylesheet" href="../../assets/css/detail.css" />
  <!-- <link rel="stylesheet" href="../../assets/css/auth.css" /> -->
</head>
<body>
    <nav class="navbar">
      <div class="nav-container">
        <a href="index.php" class="logo">
          <img src="../../assets/img/rentease.jpg" alt="Rentease Logo" />
        </a>
      </div>
    </nav>

    <main>
      <section class="property-detail">
        <?php if ($property): ?>
            <div class="detail-container">
              <div class="detail-image">
                <img
                  id="propertyImage"
                  src="../../<?= htmlspecialchars(isset($property['image_url']) && $property['image_url'] ? $property['image_url'] : 'assets/img/default-kos.jpg') ?>"
                  alt="Foto <?= htmlspecialchars($property['name']) ?>"
                />
              </div>

              <div class="detail-info">
                <h1 id="propertyName"><?= htmlspecialchars($property['name']) ?></h1>
                <p class="location" id="propertyLocation">
                    <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($property['location']) ?>" target="_blank">
                        📍 <?= htmlspecialchars($property['location']) ?>
                    </a>
                </p>
                <p class="price" id="propertyPrice">💰 Rp<?= number_format($property['price']) ?>/bulan</p>
                <p id="propertyKamar">
                    Kamar: <?= $property['available_rooms'] ?> / <?= $property['total_rooms'] ?> tersedia
                    <?= $property['available_rooms'] == 0 ? '<span style="color:red;">(Full)</span>' : '' ?>
                </p>
                <p class="desc" id="propertyDescription">
                  <?= nl2br(htmlspecialchars(isset($property['description']) && $property['description'] ? $property['description'] : 'Deskripsi belum tersedia.')) ?>
                </p>

                <div class="action-buttons">
                  <a href="booking.php?id=<?= $property['id'] ?>" id="bookingBtn" class="btn-primary" <?= $property['available_rooms'] == 0 ? 'disabled' : '' ?>>
                    <?= $property['available_rooms'] == 0 ? 'Kamar Penuh' : 'Booking Sekarang' ?>
                  </a>
                </div>
              </div>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: red; padding: 2rem;">
                Properti tidak ditemukan atau ID tidak valid. Silakan kembali ke halaman utama.
            </p>
        <?php endif; ?>
      </section>
    </main>

    <footer>
      <p>&copy; 2025 Rentease. Semua Hak Dilindungi.</p>
    </footer>
    
    <script src="../../assets/js/detail.js"></script>
    <?php if ($isLoggedIn): ?>
    <script>
      // Script untuk toggle sidebar
      const profileIcon = document.getElementById("profileIcon");
      const sidebar = document.getElementById("profileSidebar");
      const overlayProfile = document.getElementById("profileOverlay");

      if(profileIcon && sidebar && overlayProfile) {
        profileIcon.addEventListener("click", () => {
          sidebar.classList.toggle("active");
          overlayProfile.classList.toggle("active");
        });

        overlayProfile.addEventListener("click", () => {
          sidebar.classList.remove("active");
          overlayProfile.classList.remove("active");
        });
      }

      // Logout
      const logoutBtn = document.getElementById('logoutBtn');
      if(logoutBtn) {
          logoutBtn.addEventListener('click', () => {
              fetch('../../backend/api/auth.php?action=logout', {
                  method: 'POST'
              }).then(() => {
                  window.location.href = '../auth/login.php';
              });
          });
      }
    </script>
    <?php endif; ?>
</body>
</html>