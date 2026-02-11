<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/jpg" href="../../assets/img/rentease.jpg" />
    <title>Login - Rentease</title>
    <link rel="stylesheet" href="../../assets/css/style.css" />
    <link rel="stylesheet" href="../../assets/css/login.css" />
  </head>
  <body>
    <main>
      <!-- Form Login -->
      <section class="auth-left">
        <h2>Login</h2>
        <form id="loginForm">
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required />
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required />
          </div>

          <button type="submit" class="btn-primary">Login</button>
        </form>

        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
      </section>

      <!-- Logo & Deskripsi -->
      <section class="auth-right">
        <img src="../../assets/img/rentease.jpg" alt="Logo Rentease" class="logo" />
        <p>
          Rentease adalah platform penyewaan properti yang mudah, cepat, dan
          terpercaya. Temukan tempat tinggal impian Anda dengan kenyamanan
          maksimal.
        </p>
      </section>
    </main>

    <!-- Overlay Loading Spinner -->
    <div class="loading-overlay" id="loadingOverlay">
      <div class="spinner"></div>
    </div>

    

    <!-- Modal Popup Notifikasi -->
    <div id="popupNotif" class="popup-overlay">
      <div class="popup-box">
        <h3 id="popupTitle">Notifikasi</h3>
        <p id="popupMessage"></p>
        <button id="popupBtn">Tutup</button>
      </div>
    </div>
    <script src="../../assets/js/login.js"></script>
  </body>
</html>
