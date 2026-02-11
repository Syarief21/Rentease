<?php
require_once __DIR__ . '/../../backend/session.php';
require_once __DIR__ . '/../../backend/config/db.php';

Session::start();

if (Session::get('user_role') !== 'admin') {
    http_response_code(403);
    exit('Akses ditolak');
}

$admin_id = Session::get('user_id');

$sql = "SELECT b.*, 
               p.name AS property_name, 
               u.name AS user_name, 
               u.email AS user_email
        FROM bookings b
        JOIN properties p ON b.property_id = p.id
        JOIN users u ON b.user_id = u.id
        WHERE p.admin_id = ?
        ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$admin_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="confirmation">
  <h2>Konfirmasi Booking</h2>

  <table id="bookingTable">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Email</th>
        <th>Kos</th>
        <th>Jumlah</th>
        <th>Metode</th>
        <th>Bukti</th>
        <th>Tanggal Booking</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>

    <tbody id="bookingList">
      <?php if (empty($bookings)): ?>
        <tr>
          <td colspan="10" style="text-align:center">Belum ada booking</td>
        </tr>
      <?php else: ?>
        <?php foreach ($bookings as $i => $b): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($b['user_name']) ?></td>
            <td><?= htmlspecialchars($b['user_email']) ?></td>
            <td><?= htmlspecialchars($b['property_name']) ?></td>
            <td>Rp<?= number_format($b['total_amount']) ?></td>
            <td><?= htmlspecialchars($b['payment_method']) ?></td>
            <td>
              <?php if ($b['payment_proof_url']): ?>
                <img src="../../<?= $b['payment_proof_url'] ?>" class="bukti-img">
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td><?= date('d F Y H:i:s', strtotime($b['created_at'])) ?></td>

            <!-- STATUS -->
            <td>
              <?php if ($b['status'] === 'confirmed'): ?>
                <span class="status accepted">Diterima</span>
              <?php elseif ($b['status'] === 'rejected'): ?>
                <span class="status rejected">Ditolak</span>
              <?php else: ?>
                <span class="status pending">Menunggu Konfirmasi</span>
              <?php endif; ?>
            </td>

            <!-- AKSI -->
            <td>
              <?php if ($b['status'] === 'pending'): ?>
                <button class="btn btn-confirm" data-id="<?= $b['id'] ?>">Konfirmasi</button>
                <button class="btn btn-reject" data-id="<?= $b['id'] ?>">Tolak</button>
              <?php else: ?>
                <button class="btn btn-delete" data-id="<?= $b['id'] ?>">Hapus History</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</section>

<!-- MODAL -->
<div id="actionModal" class="modal-overlay">
  <div class="modal-content">
    <h3 id="modalTitle"></h3>
    <p id="modalMessage"></p>
    <div class="modal-buttons">
      <button id="modalConfirm">Ya</button>
      <button id="modalCancel">Batal</button>
    </div>
  </div>
</div>

<!-- POPUP GAMBAR -->
<div id="popupImage" class="popup-overlay">
  <span class="popup-close">&times;</span>
  <img id="popupImg">
</div>

<script src="../../assets/js/konfirmasi_booking.js"></script>
