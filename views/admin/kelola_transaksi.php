<?php
require_once __DIR__ . '/../../backend/session.php';
require_once __DIR__ . '/../../backend/config/db.php';

Session::start();

if (Session::get('user_role') !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$admin_id = Session::get('user_id');

$sql = "SELECT b.*, p.name as property_name, u.name as user_name, u.email as user_email
        FROM bookings b
        JOIN properties p ON b.property_id = p.id
        JOIN users u ON b.user_id = u.id
        WHERE p.admin_id = ?
        ORDER BY b.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$admin_id]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/jpg" href="../../assets/img/rentease.jpg" />
    <title>Kelola Transaksi - Rentease</title>
    <link rel="stylesheet" href="../../assets/css/dashboard_admin.css" />
    <link rel="stylesheet" href="../../assets/css/kelola_transaksi.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
<main class="kelola-transaksi-main">
    <section class="header-section">
        <h2>Kelola Transaksi Booking Kos</h2>
    </section>

    <section class="stat-container">
        <div class="stat-card"><h3>Total Transaksi</h3><p id="totalTransaksi">0</p></div>
        <div class="stat-card diterima"><h3>Diterima</h3><p id="totalDiterima">0</p></div>
        <div class="stat-card ditolak"><h3>Ditolak</h3><p id="totalDitolak">0</p></div>
        <div class="stat-card pending"><h3>Menunggu</h3><p id="totalMenunggu">0</p></div>
        <div class="stat-card pendapatan">
            <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                <h3>Total Pendapatan</h3>
                <div class="pendapatan-menu">
                    <div class="pendapatan-dropdown">
                        <div onclick="updatePendapatan('hari')">Hari Ini</div>
                        <div onclick="updatePendapatan('minggu')">Minggu Ini</div>
                        <div onclick="updatePendapatan('bulan')">Bulan Ini</div>
                        <div onclick="updatePendapatan('all')">Semua</div>
                    </div>
                </div>
            </div>
            <p id="totalPendapatan" style="text-align:center; margin-top:10px; font-size:1.5rem;">Rp 0</p>
        </div>
    </section>

    <section class="chart-section">
        <h3 style="text-align:center; margin-bottom:10px; color:#2c3e50">📈 Grafik Status Transaksi</h3>
        <div class="chart-wrapper" style="position:relative;">
            <canvas id="transaksiChart"></canvas>
            <button id="downloadPdfBtn" style="position:absolute; bottom:-15px; left: 50%; transform: translateX(-50%); background-color:red; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; font-size:0.9rem;">Download PDF</button>
        </div>
    </section>

    <section class="transaction-list">
        <h3>🧾 Daftar Transaksi</h3>
        <div id="transaksiContainer" class="property-grid">
            <?php if (empty($bookings)): ?>
                <p style='color:gray; text-align:center;'>Belum ada transaksi booking.</p>
            <?php else: ?>
                <?php foreach ($bookings as $p): ?>
                    <?php
                        $statusClass = 'pending';
                        if ($p['status'] === 'confirmed') $statusClass = 'available';
                        if ($p['status'] === 'rejected') $statusClass = 'full';
                    ?>
                    <div class="property-card <?= $statusClass ?>">
                        <div class="card-header"><h4><?= htmlspecialchars($p['property_name']) ?></h4></div>
                        <p><strong>Nama:</strong> <?= htmlspecialchars($p['user_name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($p['user_email']) ?></p>
                        <p><strong>Jumlah:</strong> Rp <?= number_format($p['total_amount'] ?: 0) ?></p>
                        <p><strong>Metode:</strong> <?= htmlspecialchars($p['payment_method'] ?: '-') ?></p>
                        <p><strong>Bukti:</strong> <?= $p['payment_proof_url'] ? "<img src='../../".htmlspecialchars($p['payment_proof_url'])."' class='property-image'>" : "Belum ada" ?></p>
                        <p><strong>Status:</strong> <span class="<?= $statusClass ?>"><?= htmlspecialchars($p['status']) ?></span></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>
<script>
    const serverRenderedBookings = <?= json_encode($bookings) ?>;
</script>
<script src="../../assets/js/kelola_transaksi.js"></script>
</body>
</html>
