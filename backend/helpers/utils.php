<?php
// helpers/utils.php - Fungsi-fungsi utilitas untuk aplikasi

// Fungsi untuk memeriksa apakah pengguna sudah login
function isLoggedIn() {
    session_start();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fungsi untuk mendapatkan ID pengguna yang sedang login
function getCurrentUserId() {
    session_start();
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Fungsi untuk mendapatkan role pengguna yang sedang login
function getCurrentUserRole() {
    session_start();
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

// Fungsi untuk redirect jika pengguna belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../../views/auth/login.php');
        exit;
    }
}

// Fungsi untuk redirect jika pengguna bukan admin
function requireAdmin() {
    requireLogin();
    if (getCurrentUserRole() !== 'admin') {
        header('Location: ../../views/user/index.php');
        exit;
    }
}

// Fungsi untuk sanitasi input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk upload file
function uploadFile($file, $uploadDir, $allowedTypes = ['jpg', 'jpeg', 'png']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = $file['type'];
    
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedTypes)) {
        return false;
    }
    
    if ($fileSize > 5000000) { // Maksimal 5MB
        return false;
    }
    
    $newFileName = uniqid() . '.' . $fileExtension;
    $destination = $uploadDir . $newFileName;
    
    if (move_uploaded_file($fileTmpName, $destination)) {
        return $newFileName;
    }
    
    return false;
}
?>