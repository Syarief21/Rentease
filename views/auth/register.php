<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/jpg" href="../../assets/img/rentease.jpg" />
    <title>Register - Rentease</title>
    <link rel="stylesheet" href="../../assets/css/style.css" />
    <link rel="stylesheet" href="../../assets/css/auth.css" />
    <link rel="stylesheet" href="../../assets/css/register.css" />
  </head>

  <body>
    <main>
      <!-- Kiri: Form Register -->
      <section class="auth-left">
        <h2>Daftar Akun</h2>
        <form id="registerForm">
          <div class="form-group">
            <label for="username">Nama Lengkap</label>
            <input type="text" id="username" name="username" required />
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required />
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required />
          </div>

          <button type="submit" class="btn-primary">Daftar</button>

          
        </form>

        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
      </section>

      <!-- Kanan: Logo & Deskripsi -->
      <section class="auth-right">
        <img src="../../assets/img/rentease.jpg" alt="Logo Rentease" class="logo" />
        <p>
          Rentease adalah platform penyewaan properti yang mudah, cepat, dan
          terpercaya. Temukan tempat tinggal impian Anda dengan kenyamanan
          maksimal.
        </p>
      </section>
    </main>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
      <div class="spinner"></div>
    </div>

    <!-- Modal Popup -->
    <div id="successModal" class="modal-overlay">
      <div class="modal-box">
        <h3>Pendaftaran Berhasil!</h3>
        <p>Akun Anda berhasil dibuat. Silakan login untuk melanjutkan.</p>
        <button id="closeModalBtn" class="modal-btn">Tutup</button>
      </div>
    </div>

    <script src="../../assets/js/register.js"></script>
  </body>
</html>
