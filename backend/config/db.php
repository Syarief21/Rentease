<?php
// backend/config/db.php

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Pengguna default XAMPP
define('DB_PASS', '');     // Password default XAMPP
define('DB_NAME', 'rentease'); // Nama database yang baru

// Opsi untuk koneksi PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Tampilkan error sebagai exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Hasil query sebagai associative array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Gunakan prepared statements asli
];

try {
    // Buat instance koneksi PDO
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Tangani error koneksi
    // Sebaiknya jangan tampilkan error detail di lingkungan produksi
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Koneksi database gagal. Silakan coba lagi nanti.'
        // 'error_detail' => $e->getMessage() // Uncomment untuk debugging
    ]);
    exit; // Hentikan eksekusi skrip
}

// Koneksi $pdo sekarang siap digunakan oleh file lain yang meng-includenya
// Contoh: require_once __DIR__ . '/config/db.php';