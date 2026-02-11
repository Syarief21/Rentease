<?php
require_once '../config/db.php';
require_once '../helpers/utils.php';

class BookingModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Fungsi untuk membuat booking baru
    public function createBooking($id_user, $id_property, $tanggal) {
        $id_user = (int)$id_user; // Konversi ke integer untuk keamanan
        $id_property = (int)$id_property; // Konversi ke integer untuk keamanan
        
        // Validasi tanggal
        if (!strtotime($tanggal)) {
            return false;
        }
        
        $status = 'pending';
        $query = "INSERT INTO bookings (id_user, id_property, tanggal, status) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "iiss", $id_user, $id_property, $tanggal, $status);
        
        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->conn);
        } else {
            return false;
        }
    }

    // Fungsi untuk mendapatkan semua booking
    public function getAllBookings() {
        $query = "SELECT b.*, u.nama as user_nama, p.nama_kos FROM bookings b 
                  LEFT JOIN users u ON b.id_user = u.id_user 
                  LEFT JOIN properties p ON b.id_property = p.id_property";
        $result = mysqli_query($this->conn, $query);
        
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Fungsi untuk mendapatkan booking berdasarkan user
    public function getBookingsByUser($id_user) {
        $id_user = (int)$id_user; // Konversi ke integer untuk keamanan
        $query = "SELECT b.*, p.nama_kos, p.lokasi, p.harga, p.foto FROM bookings b 
                  LEFT JOIN properties p ON b.id_property = p.id_property 
                  WHERE b.id_user = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id_user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Fungsi untuk mendapatkan booking berdasarkan property
    public function getBookingsByProperty($id_property) {
        $id_property = (int)$id_property; // Konversi ke integer untuk keamanan
        $query = "SELECT b.*, u.nama as user_nama FROM bookings b 
                  LEFT JOIN users u ON b.id_user = u.id_user 
                  WHERE b.id_property = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id_property);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Fungsi untuk mendapatkan booking berdasarkan admin
    public function getBookingsByAdmin($id_admin) {
        $id_admin = (int)$id_admin; // Konversi ke integer untuk keamanan
        $query = "SELECT b.*, u.nama as user_nama, p.nama_kos FROM bookings b 
                  LEFT JOIN users u ON b.id_user = u.id_user 
                  LEFT JOIN properties p ON b.id_property = p.id_property 
                  WHERE p.id_admin = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id_admin);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Fungsi untuk mengupdate status booking
    public function updateBookingStatus($id_booking, $status) {
        $id_booking = (int)$id_booking; // Konversi ke integer untuk keamanan
        
        // Validasi status
        if (!in_array($status, ['pending', 'confirmed', 'cancelled'])) {
            return false;
        }
        
        $query = "UPDATE bookings SET status = ? WHERE id_booking = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $status, $id_booking);
        
        return mysqli_stmt_execute($stmt);
    }

    // Fungsi untuk mendapatkan booking berdasarkan ID
    public function getBookingById($id) {
        $id = (int)$id; // Konversi ke integer untuk keamanan
        $query = "SELECT b.*, u.nama as user_nama, p.nama_kos FROM bookings b 
                  LEFT JOIN users u ON b.id_user = u.id_user 
                  LEFT JOIN properties p ON b.id_property = p.id_property 
                  WHERE b.id_booking = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_fetch_assoc($result);
    }

    // Fungsi untuk menghapus booking
    public function deleteBooking($id) {
        $id = (int)$id; // Konversi ke integer untuk keamanan
        $query = "DELETE FROM bookings WHERE id_booking = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        return mysqli_stmt_execute($stmt);
    }
}
?>