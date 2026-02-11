<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password - Rentease</title>
    <link rel="stylesheet" href="../../assets/css/style.css" />
    <link rel="stylesheet" href="../../assets/css/auth.css" />
    <link rel="stylesheet" href="../../assets/css/login.css" />
    <link rel="stylesheet" href="../../assets/css/resetpassw.css" />
  </head>
  <body>
    <main>
      <!-- Kiri: Form Reset Password -->
      <section class="auth-left">
        <h2>Reset Kata Sandi</h2>
        <p style="margin-bottom: 15px; color: #555">
          Masukkan kata sandi baru.
        </p>

        <form id="resetForm">
          <div class="form-group">
            <label for="newPassword">Kata Sandi Baru</label>
            <input
              type="password"
              id="newPassword"
              name="newPassword"
              placeholder="Kata sandi baru"
              required
            />
          </div>

          <div class="form-group">
            <label for="confirmPassword">Konfirmasi Kata Sandi</label>
            <input
              type="password"
              id="confirmPassword"
              name="confirmPassword"
              placeholder="Konfirmasi kata sandi"
              required
            />
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-reset">Simpan Kata Sandi</button>
            <a href="login.php" class="btn-back">Kembali ke Menu Login</a>
          </div>
        </form>
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

    <script src="../../assets/js/resetpasw.js"></script>
  </body>
</html>
