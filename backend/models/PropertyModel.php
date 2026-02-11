<?php
require_once '../config/db.php';
require_once '../helpers/utils.php';

class PropertyModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Fungsi untuk mendapatkan semua properti
    public function getAllProperties($search = '') {
        if (!empty($search)) {
            // Sanitasi input pencarian
            $search = sanitizeInput($search);
            $query = "SELECT p.*, u.nama as admin_nama FROM properties p LEFT JOIN users u ON p.id_admin = u.id_user WHERE p.lokasi LIKE ? OR p.nama_kos LIKE ?";
            $search_param = '%' . $search . '%';
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "ss", $search_param, $search_param);
        } else {
            $query = "SELECT p.*, u.nama as admin_nama FROM properties p LEFT JOIN users u ON p.id_admin = u.id_user";
            $stmt = mysqli_prepare($this->conn, $query);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Fungsi untuk mendapatkan properti berdasarkan ID
    public function getPropertyById($id) {
        $id = (int)$id; // Konversi ke integer untuk keamanan
        $query = "SELECT p.*, u.nama as admin_nama FROM properties p LEFT JOIN users u ON p.id_admin = u.id_user WHERE id_property = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_fetch_assoc($result);
    }

    // Fungsi untuk mendapatkan properti berdasarkan admin
    public function getPropertiesByAdmin($admin_id) {
        $admin_id = (int)$admin_id; // Konversi ke integer untuk keamanan
        $query = "SELECT * FROM properties WHERE id_admin = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $admin_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Fungsi untuk menambahkan properti baru
    public function createProperty($nama_kos, $lokasi, $harga, $deskripsi, $foto, $id_admin) {
        // Sanitasi input
        $nama_kos = sanitizeInput($nama_kos);
        $lokasi = sanitizeInput($lokasi);
        $deskripsi = sanitizeInput($deskripsi);
        $id_admin = (int)$id_admin; // Konversi ke integer untuk keamanan
        $harga = (float)$harga; // Konversi ke float untuk keamanan
        
        $query = "INSERT INTO properties (nama_kos, lokasi, harga, deskripsi, foto, id_admin) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "ssdssi", $nama_kos, $lokasi, $harga, $deskripsi, $foto, $id_admin);
        
        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->conn);
        } else {
            return false;
        }
    }

    // Fungsi untuk memperbarui properti
    public function updateProperty($id, $nama_kos, $lokasi, $harga, $deskripsi, $foto = null) {
        // Sanitasi input
        $id = (int)$id; // Konversi ke integer untuk keamanan
        $nama_kos = sanitizeInput($nama_kos);
        $lokasi = sanitizeInput($lokasi);
        $deskripsi = sanitizeInput($deskripsi);
        $harga = (float)$harga; // Konversi ke float untuk keamanan
        
        if ($foto !== null) {
            $query = "UPDATE properties SET nama_kos=?, lokasi=?, harga=?, deskripsi=?, foto=? WHERE id_property=?";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "ssdssi", $nama_kos, $lokasi, $harga, $deskripsi, $foto, $id);
        } else {
            $query = "UPDATE properties SET nama_kos=?, lokasi=?, harga=?, deskripsi=? WHERE id_property=?";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "ssdsi", $nama_kos, $lokasi, $harga, $deskripsi, $id);
        }
        
        return mysqli_stmt_execute($stmt);
    }

    // Fungsi untuk menghapus properti
    public function deleteProperty($id) {
        $id = (int)$id; // Konversi ke integer untuk keamanan
        $query = "DELETE FROM properties WHERE id_property = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        return mysqli_stmt_execute($stmt);
    }
}
?>