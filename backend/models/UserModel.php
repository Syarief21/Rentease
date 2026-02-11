<?php
require_once '../config/db.php';
require_once '../helpers/utils.php';

class UserModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Fungsi untuk register user baru
    public function register($nama, $email, $password, $role = 'user') {
        // Sanitasi input
        $nama = sanitizeInput($nama);
        $email = sanitizeInput($email);
        
        // Validasi email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $hashed_password, $role);
        
        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->conn);
        } else {
            return false;
        }
    }

    // Fungsi untuk login user
    public function login($email, $password) {
        // Sanitasi input
        $email = sanitizeInput($email);
        
        // Validasi email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }

    // Fungsi untuk mendapatkan user berdasarkan ID
    public function getUserById($id) {
        $id = (int)$id; // Konversi ke integer untuk keamanan
        $query = "SELECT * FROM users WHERE id_user = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_fetch_assoc($result);
    }
}
?>