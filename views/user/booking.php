<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/jpg" href="../../assets/img/rentease.jpg" />
  <title>Booking Kos - Rentease</title>

  <link rel="stylesheet" href="../../assets/css/style.css" />
  <link rel="stylesheet" href="../../assets/css/booking.css" />
</head>
<body>

  <!-- NAVBAR LOGO ONLY -->
  <nav class="navbar logo-only">
    <div class="nav-container">
      <a href="index.php" class="logo">
        <img src="../../assets/img/rentease.jpg" alt="Rentease Logo">
      </a>
    </div>
  </nav>

  <main>
    <section class="booking-section">
      <h2>Form Booking Kos</h2>

      <!-- INFO KOS -->
      <div id="infoKos" class="booking-info">
        <p>Memuat informasi kos...</p>
      </div>

      <div id="timeNow" class="time-display"></div>

      <form id="bookingForm">
        <!-- PENTING -->
        <input type="hidden" id="propertyId" name="property_id">

        <div class="form-group">
          <label for="namaUser">Nama Lengkap</label>
          <input
            type="text"
            id="namaUser"
            name="nama"
            placeholder="Masukkan Nama Lengkap"
            required
          />
        </div>

        <div class="form-group">
          <label for="emailUser">Email</label>
          <input
            type="email"
            id="emailUser"
            name="email"
            placeholder="Masukkan Email"
            required
          />
        </div>

        <div class="form-group">
          <label for="bookingDate">Tanggal Booking</label>
          <input type="date" id="bookingDate" name="tanggal" required />
        </div>

        <div class="form-group">
          <label for="paymentMethod">Metode Pembayaran</label>
          <select id="paymentMethod" name="paymentMethod" required>
            <option value="">-- Pilih Metode Pembayaran --</option>
            <option value="Transfer Bank BCA">Transfer Bank BCA</option>
          </select>
        </div>

        <button type="submit" class="btn-primary">
          Konfirmasi Booking
        </button>
      </form>
    </section>
  </main>

  <div id="footer-placeholder"></div>

  <script src="../../assets/js/booking.js"></script>
</body>
</html>
