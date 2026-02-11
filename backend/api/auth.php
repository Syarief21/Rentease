<?php
// backend/api/auth.php

// Set header untuk respons JSON
header('Content-Type: application/json');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Jangan tampilkan error ke output
ini_set('log_errors', '1');

// Memuat file konfigurasi dan helper yang diperlukan
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../session.php';

// Mulai sesi
Session::start();

// Ambil metode request (GET, POST, dll.)
$method = $_SERVER['REQUEST_METHOD'];

// Ambil 'action' dari query string, jika ada
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Fungsi untuk mengirim respons JSON dan menghentikan skrip
function json_response($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Routing berdasarkan metode dan aksi
switch ($method) {
    case 'POST':
        // Routing untuk permintaan POST (login, register, logout)
        switch ($action) {
            case 'register':
                // Ambil data dari body permintaan POST
                $input = json_decode(file_get_contents('php://input'), true);
                $name = isset($input['name']) ? $input['name'] : '';
                $email = isset($input['email']) ? $input['email'] : '';
                $password = isset($input['password']) ? $input['password'] : '';
                $role = isset($input['role']) ? $input['role'] : 'user'; // Default role

                // Validasi input
                if (empty($name) || empty($email) || empty($password)) {
                    json_response(['status' => 'error', 'message' => 'Semua field harus diisi.'], 400);
                }

                // Cek apakah email sudah terdaftar
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    json_response(['status' => 'error', 'message' => 'Email sudah terdaftar.'], 409);
                }

                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // Masukkan user baru ke database
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$name, $email, $hashedPassword, $role])) {
                    json_response(['status' => 'success', 'message' => 'Registrasi berhasil.']);
                } else {
                    json_response(['status' => 'error', 'message' => 'Registrasi gagal.'], 500);
                }
                break;

            case 'login':
                $input = json_decode(file_get_contents('php://input'), true);
                $email = isset($input['email']) ? $input['email'] : '';
                $password = isset($input['password']) ? $input['password'] : '';

                if (empty($email) || empty($password)) {
                    json_response(['status' => 'error', 'message' => 'Email dan password harus diisi.'], 400);
                }

                // Cari user berdasarkan email
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                // Verifikasi password
                if ($user && password_verify($password, $user['password'])) {
                    // Simpan data user ke session
                    Session::set('user_id', $user['id']);
                    Session::set('user_name', $user['name']);
                    Session::set('user_email', $user['email']);
                    Session::set('user_role', $user['role']);
                    Session::set('user_profile_picture', isset($user['profile_picture']) ? $user['profile_picture'] : null);
                    
                    json_response([
                        'status' => 'success',
                        'message' => 'Login berhasil.',
                        'data' => [
                            'userId' => $user['id'],
                            'userName' => $user['name'],
                            'userEmail' => $user['email'],
                            'userRole' => $user['role'],
                            'profile_picture' => isset($user['profile_picture']) ? $user['profile_picture'] : null,
                        ]
                    ]);
                } else {
                    json_response(['status' => 'error', 'message' => 'Email atau password salah.'], 401);
                }
                break;

            case 'logout':
                Session::destroy();
                json_response(['status' => 'success', 'message' => 'Logout berhasil.']);
                break;
            
            default:
                json_response(['status' => 'error', 'message' => 'Aksi POST tidak valid.'], 400);
                break;
        }
        break;

    case 'GET':
        // Routing untuk permintaan GET (cek status login)
        switch ($action) {
            case 'status':
                if (Session::isLoggedIn()) {
                    // Jika user sudah login, kirim data user dari session
                    json_response([
                        'status' => 'success',
                        'loggedIn' => true,
                        'data' => [
                            'userId' => Session::get('user_id'),
                            'userName' => Session::get('user_name'),
                            'email' => Session::get('user_email'),
                            'userRole' => Session::get('user_role'),
                            'profile_picture' => Session::get('user_profile_picture'),
                        ]
                    ]);
                } else {
                    // Jika tidak login
                    json_response(['status' => 'success', 'loggedIn' => false]);
                }
                break;
            
            default:
                json_response(['status' => 'error', 'message' => 'Aksi GET tidak valid.'], 400);
                break;
        }
        break;

    default:
        // Metode HTTP tidak didukung
        json_response(['status' => 'error', 'message' => 'Metode HTTP tidak didukung.'], 405);
        break;
}
