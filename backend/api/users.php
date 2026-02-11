<?php
// backend/api/users.php

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../session.php';

Session::start();

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

function require_login() {
    if (!Session::isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Silakan login terlebih dahulu.']);
        exit;
    }
}

define('UPLOAD_DIR', __DIR__ . '/../../uploads/profiles/');
if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0777, true);
}

switch ($method) {
    case 'POST':
        require_login();
        $user_id = Session::get('user_id');

        switch ($action) {
            case 'update_profile':
                $name = isset($_POST['name']) ? trim($_POST['name']) : null;
                $email = isset($_POST['email']) ? trim($_POST['email']) : null;

                if (!$name || !$email) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Nama dan email tidak boleh kosong.']);
                    exit;
                }
                
                // Validasi email format
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Format email tidak valid.']);
                    exit;
                }
                
                // Cek apakah email baru sudah digunakan oleh user lain
                $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt_check->execute([$email, $user_id]);
                if($stmt_check->fetch()) {
                    http_response_code(409);
                    echo json_encode(['status' => 'error', 'message' => 'Email sudah digunakan oleh akun lain.']);
                    exit;
                }

                $image_url = null;
                // Get current user data for reference
                $stmt_user = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
                $stmt_user->execute([$user_id]);
                $current_user_data = $stmt_user->fetch();
                
                // Handle upload file
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] != UPLOAD_ERR_NO_FILE) {
                    if ($_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
                        $file = $_FILES['profile_picture'];
                        $tmp_name = $file['tmp_name'];
                        $filename = uniqid() . '-' . basename($file['name']);
                        $destination = UPLOAD_DIR . $filename;
                        
                        // Validasi file type dengan error handling
                        $mime_type = 'application/octet-stream';
                        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                        if ($finfo) {
                            $mime_type = finfo_file($finfo, $tmp_name);
                            finfo_close($finfo);
                        }
                        
                        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        if (!in_array($mime_type, $allowed_mimes)) {
                            http_response_code(400);
                            echo json_encode(['status' => 'error', 'message' => 'Tipe file tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP. (Detected: ' . $mime_type . ')']);
                            exit;
                        }
                        
                        if (move_uploaded_file($tmp_name, $destination)) {
                            $image_url = 'uploads/profiles/' . $filename;
                        } else {
                            http_response_code(500);
                            echo json_encode(['status' => 'error', 'message' => 'Gagal mengupload file. Pastikan folder uploads/profiles/ ada dan writable.']);
                            exit;
                        }
                    } else {
                        http_response_code(400);
                        echo json_encode(['status' => 'error', 'message' => 'File upload error: ' . $_FILES['profile_picture']['error']]);
                        exit;
                    }
                }

                // Bangun query secara dinamis
                $sql_parts = [];
                $params = [];
                
                $sql_parts[] = "name = ?";
                $params[] = $name;

                $sql_parts[] = "email = ?";
                $params[] = $email;

                if ($image_url) {
                    // Hapus gambar lama jika ada dan bukan default
                    if ($current_user_data && isset($current_user_data['profile_picture']) && $current_user_data['profile_picture'] && $current_user_data['profile_picture'] !== 'default.jpg') {
                        $old_file = __DIR__ . '/../../' . $current_user_data['profile_picture'];
                        if (file_exists($old_file)) {
                            @unlink($old_file);
                        }
                    }

                    $sql_parts[] = "profile_picture = ?";
                    $params[] = $image_url;
                }

                $sql = "UPDATE users SET " . implode(', ', $sql_parts) . " WHERE id = ?";
                $params[] = $user_id;

                try {
                    $stmt = $pdo->prepare($sql);
                    if ($stmt->execute($params)) {
                        // Update session dengan data baru
                        Session::set('user_name', $name);
                        if ($image_url) {
                            Session::set('user_profile_picture', $image_url);
                        }
                        
                        // Ambil data user yang sudah diupdate untuk response
                        $stmt_updated = $pdo->prepare("SELECT id, name, email, profile_picture FROM users WHERE id = ?");
                        $stmt_updated->execute([$user_id]);
                        $updated_user = $stmt_updated->fetch();
                        
                        http_response_code(200);
                        echo json_encode([
                            'status' => 'success',
                            'message' => 'Profil berhasil diperbarui.',
                            'data' => [
                                'userId' => intval($updated_user['id']),
                                'userName' => htmlspecialchars($updated_user['name']),
                                'userEmail' => htmlspecialchars($updated_user['email']),
                                'profile_picture' => $updated_user['profile_picture'],
                                'profile_picture_url' => $updated_user['profile_picture'] ? $updated_user['profile_picture'] : 'assets/img/default.jpg'
                            ]
                        ]);
                    } else {
                        http_response_code(500);
                        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui profil di database.']);
                    }
                } catch (PDOException $e) {
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
                }
                break;
            
            default:
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Aksi POST tidak valid.']);
                break;
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Metode HTTP tidak didukung.']);
        break;
}
