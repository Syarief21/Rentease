<!DOCTYPE html>
<html lang="id">
  <head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/jpg" href="../../assets/img/rentease.jpg" />
  <title>Konfirmasi Pembayaran - Rentease</title>
    <link rel="stylesheet" href="../../assets/css/style.css" />
    <link rel="stylesheet" href="../../assets/css/payment.css" />
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
      <section class="payment-section">
        <h2>Konfirmasi Pembayaran</h2>

        <!-- FORM PAYMENT -->
        <form
          class="payment-card"
          id="paymentForm"
          enctype="multipart/form-data"
        >
          <p id="paymentInfo">Silakan transfer ke rekening berikut:</p>

          <div class="payment-details">
            <p><strong>Nama:</strong> <span id="userName">-</span></p>
            <p><strong>Email:</strong> <span id="userEmail">-</span></p>
            <p><strong>Bank:</strong> BCA</p>
            <p><strong>Atas Nama:</strong> Rentease</p>
            <p><strong>No Rek Admin:</strong> 12345678</p>
            <p><strong>Jumlah:</strong> <span id="paymentAmount">Rp -</span></p>
            <p>
              <strong>Tanggal Booking:</strong> <span id="bookingDate">-</span>
            </p>
            <p>
              <strong>Tanggal Pilihan:</strong> <span id="selectedDate">-</span>
            </p>
          </div>

          <div class="payment-upload">
            <label for="buktiPembayaran">Upload Bukti Pembayaran:</label>
            <input
              type="file"
              id="buktiPembayaran"
              name="buktiPembayaran"
              accept="image/*"
              required
            />
            <!-- PREVIEW BUKTI PEMBAYARAN -->
            <img
              id="previewBukti"
              src=""
              alt="Preview Bukti Pembayaran"
              style="
                display: none;
                margin-top: 10px;
                width: 250px;
                border-radius: 8px;
              "
            />
            <p id="uploadStatus" style="color: green; font-size: 0.9rem"></p>
          </div>

          <div class="payment-actions">
            <button type="submit" class="btn-primary">Sudah Bayar</button>
          </div>
        </form>
      </section>
    </main>

    <!-- LOADING OVERLAY -->
    <div id="loadingOverlay">
      <div class="hourglass"></div>
      <div id="loadingText">Memproses Pembayaran Anda...</div>
    </div>

    <div id="footer-placeholder"></div>
    <script src="../../assets/js/payment.js"></script>
  </body>
</html>
