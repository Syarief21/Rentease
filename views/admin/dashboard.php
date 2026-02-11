<?php
require_once __DIR__ . '/../../backend/session.php';
require_once __DIR__ . '/../../backend/config/db.php';

Session::start();

// Cek jika user bukan admin, redirect ke halaman login
if (Session::get('user_role') !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$admin_id = Session::get('user_id');
$admin_name = Session::get('user_name');

// Ambil data admin untuk foto profil
$stmt_user = $pdo->prepare("SELECT name, email, profile_picture FROM users WHERE id = ?");
$stmt_user->execute([$admin_id]);
$admin_user = $stmt_user->fetch();
$admin_photo = isset($admin_user['profile_picture']) ? $admin_user['profile_picture'] : 'assets/img/profile.jpg';
$admin_email = isset($admin_user['email']) ? $admin_user['email'] : '';


// Ambil semua properti milik admin ini
$stmt_props = $pdo->prepare("SELECT * FROM properties WHERE admin_id = ? ORDER BY created_at DESC");
$stmt_props->execute([$admin_id]);
$properties = $stmt_props->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/jpg" href="../../assets/img/rentease.jpg" />
    <title>Dashboard Admin - Rentease</title>
    <link rel="stylesheet" href="../../assets/css/dashboard_admin.css" />
    <link rel="stylesheet" href="../../assets/css/hapuskosadm.css" />
    <link rel="stylesheet" href="../../assets/css/editprofiladmin.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
    <aside class="sidebar">
      <div>
        <div class="admin-profile">
          <img src="../../<?= htmlspecialchars($admin_photo) ?>" alt="Foto Admin" />
          <p>Selamat Datang, <br><strong><?= htmlspecialchars($admin_name) ?></strong>!</p>
        </div>

        <div class="menu">
          <a href="#" class="sidebar-link active" data-page="dashboard"><i class="fa-solid fa-house"></i><span>Beranda</span></a>
          <a href="#" class="sidebar-link" data-page="tambahKos"><i class="fa-solid fa-circle-plus"></i><span>Tambah Kos</span></a>
          <a href="#" class="sidebar-link" data-page="konfirmasi"><i class="fa-solid fa-check-to-slot"></i><span>Konfirmasi Booking</span><span class="notif-badge" id="badgeKonfirmasi" style="display: none">0</span></a>
          <a href="#" class="sidebar-link" data-page="kelolaTransaksi"><i class="fa-solid fa-money-bill-wave"></i><span>Kelola Transaksi</span></a>
          <a href="#" class="sidebar-link" data-page="pesanMasuk"><i class="fa-solid fa-inbox"></i><span>Pesan Masuk</span><span class="notif-badge" id="badgePesan" style="display: none">0</span></a>
        </div>
      </div>
      <button class="logout-btn"><svg class="logout-icon" viewBox="0 0 24 24"><path d="M14 3H6a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h8v-2H7V5h7V3zm4 8h-5v2h5v3l4-4-4-4v3z" /></svg>Logout</button>
    </aside>

    <nav class="navbar">
      <div class="logo"><a href="#">Dashboard Pemilik Kos</a></div>
      <div class="datetime" id="datetime"></div>
    </nav>

    <main id="mainContent" class="main-content">
      <section class="admin-dashboard" id="dashboard">
        <h2>Daftar Kos Saya</h2>
        <div id="adminProperties" class="property-grid">
            <?php if (empty($properties)): ?>
                <p>Belum ada kos yang ditambahkan.</p>
            <?php else: ?>
                <?php foreach($properties as $p): ?>
                    <div class="property-card <?= $p['available_rooms'] == 0 ? 'full' : 'available' ?>" data-id="<?= $p['id'] ?>">
                        <div class="card-header">
                            <h4><?= htmlspecialchars($p['name']) ?></h4>
                            <div class="card-menu">
                                <button class="menu-btn" data-id="<?= $p['id'] ?>">⋮</button>
                                <div class="menu-dropdown" id="menu-<?= $p['id'] ?>">
                                    <button class="delete-btn" data-id="<?= $p['id'] ?>">Hapus Kos</button>
                                    <button class="edit-btn" data-id="<?= $p['id'] ?>">Edit Kos</button>
                                </div>
                            </div>
                        </div>
                        <img src="../../<?= htmlspecialchars(isset($p['image_url']) && $p['image_url'] ? $p['image_url'] : 'assets/img/default.jpg') ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="property-image">
                        <p><span class="map-icon">📍</span><?= htmlspecialchars($p['location']) ?></p>
                        <p>Harga: Rp <?= number_format($p['price'], 0, ',', '.') ?></p>
                        <p>Deskripsi: <?= htmlspecialchars(isset($p['description']) && $p['description'] ? $p['description'] : '-') ?></p>
                        <p>Kamar:
                            <span class="<?= $p['available_rooms'] == 0 ? 'kamar-full' : 'kamar-available' ?>">
                                <?= $p['available_rooms'] ?> / <?= $p['total_rooms'] ?> tersedia
                                <?= $p['available_rooms'] == 0 ? " (Full)" : "" ?>
                            </span>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
      </section>
    </main>

    <!-- Popups -->
    <div id="confirmDeletePopup" class="confirm-popup"><div class="confirm-box"><h3>Hapus Kos?</h3><p>Apakah anda yakin ingin menghapus kos ini?<br>Data akan hilang permanen.</p><div class="confirm-actions"><button id="cancelDeleteBtn" class="btn-cancel">Batal</button><button id="confirmDeleteBtn" class="btn-delete">Hapus</button></div></div></div>
    <div class="edit-popup" id="editPopup"><h3>Edit Kos</h3><form id="editForm" enctype="multipart/form-data">
        <label>Nama Kos</label>
        <input type="text" id="editNama" name="name" required />
        <label>Alamat</label>
        <input type="text" id="editAlamat" name="location" required />
        <label>Harga</label>
        <input type="number" id="editHarga" name="price" required />
        <label>Total Kamar</label>
        <input type="number" id="editTotalKamar" name="total_rooms" required />
        <label>Kamar Tersedia</label>
        <input type="number" id="editTersedia" name="available_rooms" required />
        <label>Deskripsi</label>
        <textarea id="editDeskripsi" name="description" required></textarea>
        <label>Foto Kos</label>
        <input type="file" id="editFoto" name="image" accept="image/*" />
        <div style="text-align: right; margin-top: 15px;">
            <button type="submit" class="btn-save">Simpan</button>
            <button type="button" id="editCancel" class="btn-cancel">Batal</button>
        </div>
    </form></div>
    
    <!-- Edit Profile Modal -->
    <div id="modalEditProfil" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
      <div class="modal-content" style="background:white; padding:20px; border-radius:8px; width:400px; max-width:90%;">
        <h3>Edit Profil Admin</h3>
        <form id="formEditProfil" enctype="multipart/form-data">
          <div style="margin-bottom:15px; text-align:center;">
             <img id="previewFotoProfil" src="" alt="Preview" style="width:100px; height:100px; border-radius:50%; object-fit:cover; margin-bottom:10px;">
          </div>
          <label>Nama</label>
          <input type="text" id="inputNamaProfil" name="name" required style="width:100%; padding:8px; margin-bottom:10px;">
          <label>Email</label>
          <input type="email" id="inputEmailProfil" name="email" required style="width:100%; padding:8px; margin-bottom:10px;">
          <label>Foto Profil</label>
          <input type="file" id="inputFotoProfil" name="profile_picture" accept="image/*" style="width:100%; margin-bottom:15px;">
          <div style="text-align:right;">
            <button type="submit" style="padding:8px 16px; background:#4CAF50; color:white; border:none; border-radius:4px; cursor:pointer;">Simpan</button>
            <button type="button" id="btnBatalProfil" style="padding:8px 16px; background:#f44336; color:white; border:none; border-radius:4px; cursor:pointer; margin-left:10px;">Batal</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Success Modal -->
    <div id="modalSukses" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000; justify-content:center; align-items:center;">
        <div class="modal-content" style="background:white; padding:20px; border-radius:8px; text-align:center;">
            <h3>Berhasil!</h3>
            <p>Profil berhasil diperbarui.</p>
            <button id="btnOkSukses" style="padding:8px 16px; background:#4CAF50; color:white; border:none; border-radius:4px; cursor:pointer;">OK</button>
        </div>
    </div>
    
    <!-- Scripts -->
    <script>
        const serverRenderedAdminProperties = <?= json_encode($properties) ?>;
        const serverRenderedAdminProfile = <?= json_encode($admin_user) ?>;
    </script>
    <script src="../../assets/js/logout.js"></script>
    <script src="../../assets/js/utilsadm.js"></script>
    <script src="../../assets/js/dashboardkosadm.js"></script>
    <script src="../../assets/js/tambahkosadm.js"></script>
    <script src="../../assets/js/konfirmasiadm.js"></script>
    <script src="../../assets/js/kelolatransaksiadm.js"></script>
    <script src="../../assets/js/pesanmasukadm.js"></script>
    <script src="../../assets/js/navigationadm.js"></script>
    <script src="../../assets/js/editprofiladm.js"></script>
</body>
</html>