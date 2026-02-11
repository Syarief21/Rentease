<?php
// import_db.php - Script untuk import database

$mysqli = new mysqli("localhost", "root", "", "");

if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Baca file SQL
$sql = file_get_contents(__DIR__ . '/database.sql');

// Eksekusi multiple queries
if ($mysqli->multi_query($sql)) {
    echo "Database berhasil di-import!";
    while ($mysqli->next_result()) {
        // Loop untuk menangani multiple queries
    }
} else {
    echo "Error: " . $mysqli->error;
}

$mysqli->close();
?>
