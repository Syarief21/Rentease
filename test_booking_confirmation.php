<?php
/**
 * Test script untuk konfirmasi booking admin
 * Akses: http://localhost/Project/test_booking_confirmation.php
 */

require_once __DIR__ . '/backend/config/db.php';
require_once __DIR__ . '/backend/session.php';

Session::start();

// Check if logged in as admin
if (!Session::isLoggedIn() || Session::get('user_role') !== 'admin') {
    echo "<div style='background:#f8d7da; padding:20px; border-radius:5px; color:#721c24;'>";
    echo "<h3>⚠️ Akses Ditolak</h3>";
    echo "<p>Anda harus login sebagai admin untuk mengakses halaman ini.</p>";
    echo "<p><a href='views/auth/login.php'>Login di sini</a></p>";
    echo "</div>";
    exit;
}

$admin_id = Session::get('user_id');

// Get all bookings for admin's properties
$sql = "SELECT b.*, p.name as property_name, u.name as user_name, u.email as user_email
        FROM bookings b
        JOIN properties p ON b.property_id = p.id
        JOIN users u ON b.user_id = u.id
        WHERE p.admin_id = ?
        ORDER BY b.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$admin_id]);
$bookings = $stmt->fetchAll();

// Get statistics
$pending_count = count(array_filter($bookings, function($b) { return $b['status'] === 'pending'; }));
$confirmed_count = count(array_filter($bookings, function($b) { return $b['status'] === 'confirmed'; }));
$rejected_count = count(array_filter($bookings, function($b) { return $b['status'] === 'rejected'; }));

?>
<!DOCTYPE html>
<html>
<head>
    <title>Booking Confirmation Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        h3 { color: #555; margin-top: 20px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { background: #f8f9fa; padding: 15px; border-radius: 4px; border-left: 4px solid #007bff; }
        .stat-value { font-size: 24px; font-weight: bold; color: #007bff; }
        .stat-label { color: #666; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #007bff; color: white; }
        tr:hover { background: #f9f9f9; }
        .status { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .status.pending { background: #fff3cd; color: #856404; }
        .status.confirmed { background: #d4edda; color: #155724; }
        .status.rejected { background: #f8d7da; color: #721c24; }
        button { padding: 6px 12px; margin: 2px; cursor: pointer; border: none; border-radius: 4px; font-size: 12px; }
        .btn-confirm { background: #28a745; color: white; }
        .btn-reject { background: #dc3545; color: white; }
        .btn-confirm:hover { background: #218838; }
        .btn-reject:hover { background: #c82333; }
        .test-log { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0; font-family: monospace; max-height: 300px; overflow-y: auto; }
        .test-result { padding: 10px; margin: 5px 0; border-radius: 3px; }
        .test-ok { background: #d4edda; color: #155724; }
        .test-fail { background: #f8d7da; color: #721c24; }
        .info-box { background: #e7f3ff; padding: 15px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #2196F3; }
    </style>
</head>
<body>
<div class="container">
    <h1>🧪 Booking Confirmation Test Panel</h1>
    <p>Selamat datang, <strong><?= htmlspecialchars(Session::get('user_name')) ?></strong>!</p>
    
    <h2>📊 Statistik Booking</h2>
    <div class="stats">
        <div class="stat-card">
            <div class="stat-value" style="color: #ffc107;"><?= $pending_count ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #28a745;"><?= $confirmed_count ?></div>
            <div class="stat-label">Confirmed</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #dc3545;"><?= $rejected_count ?></div>
            <div class="stat-label">Rejected</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= count($bookings) ?></div>
            <div class="stat-label">Total</div>
        </div>
    </div>

    <h2>📋 Daftar Booking</h2>
    
    <?php if (empty($bookings)): ?>
        <div class="info-box">
            <strong>ℹ️ Info:</strong> Belum ada booking untuk properti Anda.
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Penyewa</th>
                    <th>Email</th>
                    <th>Kos</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Test Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($bookings as $index => $booking): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($booking['user_name']) ?></td>
                    <td><?= htmlspecialchars($booking['user_email']) ?></td>
                    <td><?= htmlspecialchars($booking['property_name']) ?></td>
                    <td>Rp <?= number_format($booking['total_amount'] ?: 0) ?></td>
                    <td><?= htmlspecialchars($booking['payment_method'] ?: '-') ?></td>
                    <td><span class="status <?= htmlspecialchars(strtolower($booking['status'])) ?>"><?= htmlspecialchars($booking['status']) ?></span></td>
                    <td><?= date('d M Y', strtotime($booking['created_at'])) ?></td>
                    <td>
                        <?php if($booking['status'] === 'pending'): ?>
                            <button class="btn-confirm" onclick="testConfirm(<?= $booking['id'] ?>)">Confirm</button>
                            <button class="btn-reject" onclick="testReject(<?= $booking['id'] ?>)">Reject</button>
                        <?php else: ?>
                            <span style="color: #999;">No Action</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2>🔍 API Test</h2>
    <div class="info-box">
        <strong>Test Update Status</strong>
        <p>
            Booking ID: <input type="number" id="testBookingId" placeholder="Masukkan booking ID" value="<?= isset($bookings[0]) ? $bookings[0]['id'] : '' ?>">
            <button onclick="testUpdateStatus('confirmed')">Test Confirm</button>
            <button onclick="testUpdateStatus('rejected')">Test Reject</button>
        </p>
    </div>

    <div class="test-log" id="testLog"></div>

    <h2>📝 Debugging Info</h2>
    <table>
        <tr>
            <th>Parameter</th>
            <th>Value</th>
        </tr>
        <tr>
            <td>Admin ID</td>
            <td><?= $admin_id ?></td>
        </tr>
        <tr>
            <td>Total Bookings</td>
            <td><?= count($bookings) ?></td>
        </tr>
        <tr>
            <td>Pending Count</td>
            <td><?= $pending_count ?></td>
        </tr>
        <tr>
            <td>Database Connection</td>
            <td><span style="color: green;">✓ Connected</span></td>
        </tr>
    </table>
</div>

<script>
const testLog = document.getElementById('testLog');

function log(message, type = 'info') {
    const timestamp = new Date().toLocaleTimeString();
    const color = type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : '#007bff';
    const icon = type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️';
    testLog.innerHTML += `<div style="color: ${color};">[${timestamp}] ${icon} ${message}</div>`;
    testLog.scrollTop = testLog.scrollHeight;
}

function testConfirm(id) {
    if (!confirm('Test confirm booking ' + id + '?')) return;
    testUpdateStatus('confirmed', id);
}

function testReject(id) {
    if (!confirm('Test reject booking ' + id + '?')) return;
    testUpdateStatus('rejected', id);
}

async function testUpdateStatus(status, bookingId = null) {
    const id = bookingId || document.getElementById('testBookingId').value;
    
    if (!id) {
        log('Booking ID tidak boleh kosong!', 'error');
        return;
    }
    
    log(`Sending ${status} request for booking ${id}...`);
    
    try {
        const response = await fetch('../../backend/api/bookings.php?action=update_status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                booking_id: parseInt(id), 
                status: status 
            })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            log(`✓ Successfully updated to ${status}`, 'success');
            log('Refreshing page...');
            setTimeout(() => location.reload(), 1500);
        } else {
            log(`✗ ${data.message}`, 'error');
        }
    } catch (error) {
        log(`✗ Network error: ${error.message}`, 'error');
    }
}

window.addEventListener('load', () => {
    log('Test panel loaded. Ready to test booking confirmation.');
    log(`Total bookings: <?= count($bookings) ?>`);
});
</script>
</body>
</html>
