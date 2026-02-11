<?php
/**
 * Test script untuk admin dashboard - edit dan delete kos
 * Akses: http://localhost/Project/test_admin_dashboard.php
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

// Get admin's properties
$stmt = $pdo->prepare("SELECT * FROM properties WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$properties = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        h3 { color: #555; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #007bff; color: white; }
        tr:hover { background: #f9f9f9; }
        .status-success { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        button { padding: 8px 16px; margin: 5px; cursor: pointer; border: none; border-radius: 4px; }
        .btn-edit { background: #007bff; color: white; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-edit:hover { background: #0056b3; }
        .btn-delete:hover { background: #c82333; }
        .test-log { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0; font-family: monospace; max-height: 300px; overflow-y: auto; }
        .test-result { padding: 10px; margin: 5px 0; border-radius: 3px; }
        .test-ok { background: #d4edda; color: #155724; }
        .test-fail { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container">
    <h1>🧪 Admin Dashboard Test Panel</h1>
    <p>Selamat datang, <strong><?= htmlspecialchars(Session::get('user_name')) ?></strong>!</p>
    
    <h2>📋 Daftar Kos Anda</h2>
    
    <?php if (empty($properties)): ?>
        <div style="background: #fff3cd; padding: 15px; border-radius: 4px; color: #856404;">
            <strong>ℹ️ Info:</strong> Belum ada kos yang ditambahkan.
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Kos</th>
                    <th>Lokasi</th>
                    <th>Harga</th>
                    <th>Kamar</th>
                    <th>Aksi Test</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($properties as $prop): ?>
                <tr>
                    <td><?= $prop['id'] ?></td>
                    <td><?= htmlspecialchars($prop['name']) ?></td>
                    <td><?= htmlspecialchars($prop['location']) ?></td>
                    <td>Rp <?= number_format($prop['price']) ?></td>
                    <td><?= $prop['available_rooms'] ?> / <?= $prop['total_rooms'] ?></td>
                    <td>
                        <button class="btn-edit" onclick="testEditProperty(<?= $prop['id'] ?>)">Test Edit</button>
                        <button class="btn-delete" onclick="testDeleteProperty(<?= $prop['id'] ?>)">Test Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2>🔍 API Test</h2>
    <div>
        <label>Test Get Property by ID:</label>
        <input type="number" id="testId" placeholder="Masukkan ID kos" value="<?= isset($properties[0]) ? $properties[0]['id'] : '' ?>">
        <button onclick="testGetProperty()">Test GET</button>
    </div>

    <div class="test-log" id="testLog"></div>

    <h2>📊 API Endpoints</h2>
    <table>
        <tr>
            <th>Method</th>
            <th>Endpoint</th>
            <th>Deskripsi</th>
        </tr>
        <tr>
            <td>GET</td>
            <td>/backend/api/properties.php?id={id}</td>
            <td>Get single property</td>
        </tr>
        <tr>
            <td>GET</td>
            <td>/backend/api/properties.php</td>
            <td>Get all properties for admin</td>
        </tr>
        <tr>
            <td>POST</td>
            <td>/backend/api/properties.php</td>
            <td>Create or update property (with id in POST data)</td>
        </tr>
        <tr>
            <td>DELETE</td>
            <td>/backend/api/properties.php?id={id}</td>
            <td>Delete property</td>
        </tr>
    </table>

    <h2>📝 Test Results</h2>
    <div id="testResults"></div>
</div>

<script>
const testLog = document.getElementById('testLog');
const testResults = document.getElementById('testResults');

function log(message, type = 'info') {
    const timestamp = new Date().toLocaleTimeString();
    const color = type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : '#007bff';
    const icon = type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️';
    testLog.innerHTML += `<div style="color: ${color};">[${timestamp}] ${icon} ${message}</div>`;
    testLog.scrollTop = testLog.scrollHeight;
}

async function testGetProperty() {
    const id = document.getElementById('testId').value;
    if (!id) {
        log('ID tidak boleh kosong!', 'error');
        return;
    }
    
    log(`Fetching property ${id}...`);
    try {
        const response = await fetch(`../../backend/api/properties.php?id=${id}`);
        const data = await response.json();
        
        if (data.status === 'success') {
            log(`✓ Property found: ${data.data.name}`, 'success');
            log(`Data: ${JSON.stringify(data.data, null, 2)}`);
        } else {
            log(`✗ ${data.message}`, 'error');
        }
    } catch (error) {
        log(`✗ Error: ${error.message}`, 'error');
    }
}

function testEditProperty(id) {
    log(`Opening edit form for property ${id}...`);
    
    // Simulate what dashboard.js does
    fetch(`../../backend/api/properties.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                log(`✓ Edit data loaded for: ${data.data.name}`, 'success');
                log(`Ready to edit. Fields: name, location, price, total_rooms, available_rooms, description`);
            } else {
                log(`✗ Failed to load: ${data.message}`, 'error');
            }
        })
        .catch(err => log(`✗ Fetch error: ${err.message}`, 'error'));
}

function testDeleteProperty(id) {
    if (!confirm(`Apakah yakin menghapus properti ${id}?`)) return;
    
    log(`Deleting property ${id}...`);
    fetch(`../../backend/api/properties.php?id=${id}`, {
        method: 'DELETE'
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                log(`✓ Property deleted: ${data.message}`, 'success');
                log('Refresh page to see updated list');
            } else {
                log(`✗ Delete failed: ${data.message}`, 'error');
            }
        })
        .catch(err => log(`✗ Fetch error: ${err.message}`, 'error'));
}

// Auto-load on page load
window.addEventListener('load', () => {
    log('Test panel loaded. Ready to test API endpoints.');
    log(`Total properties: <?= count($properties) ?>`);
});
</script>
</body>
</html>
