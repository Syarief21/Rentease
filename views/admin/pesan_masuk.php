<section class="admin-dashboard" id="pesanMasuk">
  <h2>📩 Pesan Masuk</h2>

  <!-- CSS halaman -->
  <link rel="stylesheet" href="../../assets/css/pesan_masuk.css" />

  <!-- Tombol hapus semua -->
  <button id="deleteAllBtn" class="delete-all-btn">Hapus Semua Pesan</button>

  <!-- LIST PESAN -->
  <div id="messagesContainer" class="message-grid">
    <!-- Pesan akan dirender otomatis oleh pesan_masuk.js -->
  </div>

  <!-- ====================== POPUP BALASAN ======================= -->
  <div id="replyPopup" class="reply-popup" style="display: none;">
    <div class="reply-box">
      <h3>Balas Pesan</h3>
      <p id="replyTo"></p>

      <textarea 
        id="replyMessage" 
        placeholder="Tulis balasan Anda..." 
        rows="4">
      </textarea>

      <div class="reply-actions">
        <button id="sendReplyBtn" class="send-btn">Kirim</button>
        <button id="cancelReplyBtn" class="cancel-btn">Batal</button>
      </div>
    </div>
  </div>

  <!-- ====================== POPUP NOTIFIKASI ======================= -->
  <div id="notifPopup" class="notif-popup" style="display: none;">
    <div class="notif-box">
      <p id="notifMessage"></p>
    </div>
  </div>

  <!-- ====================== MODAL KONFIRMASI ======================= -->
  <div id="confirmModal" class="confirm-modal" style="display: none;">
    <div class="confirm-box">
      <p id="confirmMessage">Apakah Anda yakin?</p>

      <div class="confirm-actions">
        <button id="confirmYes" class="confirm-yes">Ya</button>
        <button id="confirmNo" class="confirm-no">Tidak</button>
      </div>
    </div>
  </div>

  <!-- SCRIPT UTAMA -->
  <script src="../../assets/js/pesan_masuk.js"></script>
</section>
