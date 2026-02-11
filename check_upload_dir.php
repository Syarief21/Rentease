<?php
// check_upload_dir.php - Cek folder upload

$uploadDirs = [
    'uploads/profiles/' => 'Profile Pictures',
    'uploads/property/' => 'Property Images',
    'uploads/proof/' => 'Payment Proof'
];

echo "<h2>Checking Upload Directories</h2>";

foreach ($uploadDirs as $dir => $name) {
    $fullPath = __DIR__ . '/' . $dir;
    echo "<p><strong>$name ($dir)</strong><br>";
    
    if (file_exists($fullPath)) {
        echo "✓ Direktori ada<br>";
        
        if (is_writable($fullPath)) {
            echo "✓ Writable (bisa upload)<br>";
        } else {
            echo "✗ Tidak writable (perlu chmod)<br>";
            echo "Solusi: chmod -R 777 " . str_replace('\\', '/', $fullPath) . "<br>";
        }
    } else {
        echo "✗ Direktori tidak ada<br>";
        if (@mkdir($fullPath, 0777, true)) {
            echo "✓ Direktori berhasil dibuat<br>";
        } else {
            echo "✗ Gagal membuat direktori<br>";
        }
    }
    echo "</p>";
}

// Test file upload
echo "<h2>Test Upload</h2>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    $file = $_FILES['test_file'];
    $uploadDir = __DIR__ . '/uploads/profiles/';
    $filename = uniqid() . '-' . basename($file['name']);
    $destination = $uploadDir . $filename;
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            echo "<p>✓ File berhasil diupload: " . htmlspecialchars($filename) . "</p>";
            unlink($destination); // Hapus file test
        } else {
            echo "<p>✗ Gagal upload file</p>";
        }
    } else {
        echo "<p>✗ Error upload: " . $file['error'] . "</p>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <label>Test Upload File:</label>
    <input type="file" name="test_file" required>
    <button type="submit">Test Upload</button>
</form>
