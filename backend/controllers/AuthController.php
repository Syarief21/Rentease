<?php
require_once '../config/db.php';
require_once '../models/UserModel.php';
require_once '../session.php';

// Tangkap error PHP dan ubah ke JSON
function handleError($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $error = [
        'success' => false,
        'message' => "Error: $errstr",
        'file' => $errfile,
        'line' => $errline
    ];
    
    header('Content-Type: application/json');
    echo json_encode($error);
    exit;
}

set_error_handler("handleError");

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    if ($action === 'login') {
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Validasi input
        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Email dan password harus diisi']);
            exit;
        }
        
        $userModel = new UserModel($conn);
        $user = $userModel->login($email, $password);
        
        if ($user) {
            // Set sesi
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_name'] = $user['nama'];
            
            // Return data JSON untuk JavaScript
            if ($user['role'] === 'admin') {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Login berhasil sebagai admin',
                    'userId' => $user['id_user'],
                    'role' => $user['role'],
                    'userName' => $user['nama']
                ]);
            } else {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Login berhasil',
                    'userId' => $user['id_user'],
                    'role' => $user['role'],
                    'userName' => $user['nama']
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Email atau password salah']);
        }
    } elseif ($action === 'register') {
        $nama = isset($_POST['nama']) ? $_POST['nama'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        // Validasi input
        if (empty($nama) || empty($email) || empty($password) || empty($confirm_password)) {
            echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
            exit;
        }
        
        if ($password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Password dan konfirmasi password tidak cocok']);
            exit;
        }
        
        // Validasi password
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter']);
            exit;
        }
        
        $userModel = new UserModel($conn);
        $result = $userModel->register($nama, $email, $password);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Registrasi berhasil']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
        }
    } elseif ($action === 'logout') {
        if (doLogout()) {
            echo json_encode(['success' => true, 'message' => 'Logout berhasil']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal logout']);
        }
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
    }
} catch (Exception $e) {
    $error = [
        'success' => false,
        'message' => 'Exception: ' . $e->getMessage()
    ];
    header('Content-Type: application/json');
    echo json_encode($error);
    exit;
}
?>