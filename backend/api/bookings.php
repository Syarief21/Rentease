<?php
// backend/api/bookings.php

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../session.php';

Session::start();

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null; // Bisa GET atau POST

// Fungsi untuk otentikasi pengguna yang sudah login
function require_login() {
    if (!Session::isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Silakan login terlebih dahulu.']);
        exit;
    }
}

// Lokasi folder untuk upload bukti pembayaran
define('UPLOAD_DIR', __DIR__ . '/../../uploads/proof/');
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}


switch ($method) {
    case 'GET':
        require_login();
        $user_id = Session::get('user_id');
        $user_role = Session::get('user_role');
        $booking_id = isset($_GET['id']) ? $_GET['id'] : null;

        if ($booking_id) {
            // Ambil satu data booking spesifik
            $sql = "SELECT b.*, p.name as property_name, p.price, u.name as user_name, u.email as user_email
                    FROM bookings b
                    JOIN properties p ON b.property_id = p.id
                    JOIN users u ON b.user_id = u.id
                    WHERE b.id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$booking_id]);
            $booking = $stmt->fetch();

            if (!$booking) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Booking tidak ditemukan.']);
                exit;
            }

            // Keamanan: user hanya bisa lihat booking miliknya, admin bisa lihat semua (bisa diperketat)
            if ($user_role !== 'admin' && $booking['user_id'] != $user_id) {
                 http_response_code(403);
                 echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
                 exit;
            }

            echo json_encode(['status' => 'success', 'data' => $booking]);

        } else {
            // Ambil daftar booking (logika yang sudah ada)
            if ($user_role === 'admin') {
                $sql = "SELECT b.*, p.name as property_name, u.name as user_name, u.email as user_email
                        FROM bookings b
                        JOIN properties p ON b.property_id = p.id
                        JOIN users u ON b.user_id = u.id
                        WHERE p.admin_id = ?
                        ORDER BY b.created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_id]);
            } else {
                $sql = "SELECT b.*, p.name as property_name, p.location, p.price
                        FROM bookings b
                        JOIN properties p ON b.property_id = p.id
                        WHERE b.user_id = ?
                        ORDER BY b.created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_id]);
            }
            
            $bookings = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $bookings]);
        }
        break;

    case 'POST':
        require_login();
        $user_id = Session::get('user_id');

        switch ($action) {
            case 'create':
                // Membuat entri booking awal (tanpa bukti bayar)
                $input = json_decode(file_get_contents('php://input'), true);
                $property_id = isset($input['property_id']) ? $input['property_id'] : null;
                $booking_date = isset($input['booking_date']) ? $input['booking_date'] : null;
                $payment_method = isset($input['payment_method']) ? $input['payment_method'] : null;

                if (!$property_id || !$booking_date || !$payment_method) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Data untuk membuat booking tidak lengkap.']);
                    exit;
                }

                $sql = "INSERT INTO bookings (user_id, property_id, booking_date, payment_method, status) VALUES (?, ?, ?, ?, 'pending')";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$user_id, $property_id, $booking_date, $payment_method])) {
                    http_response_code(201);
                    echo json_encode([
                        'status' => 'success', 
                        'message' => 'Booking berhasil dibuat, silakan lanjutkan ke pembayaran.',
                        'booking_id' => $pdo->lastInsertId()
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => 'Gagal membuat booking.']);
                }
                break;

            case 'submit_payment':
                // Mengirimkan bukti pembayaran untuk booking yang sudah ada
                $booking_id = isset($_POST['booking_id']) ? $_POST['booking_id'] : null;
                $total_amount = isset($_POST['total_amount']) ? $_POST['total_amount'] : null;

                if (!$booking_id || !$total_amount) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'ID Booking dan total harga diperlukan.']);
                    exit;
                }

                // Cek apakah user pemilik booking ini
                $stmt_check = $pdo->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ?");
                $stmt_check->execute([$booking_id, $user_id]);
                if(!$stmt_check->fetch()){
                    http_response_code(403);
                    echo json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki izin untuk booking ini.']);
                    exit;
                }

                // Handle upload bukti pembayaran
                $payment_proof_url = null;
                if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['payment_proof']['tmp_name'];
                    $filename = uniqid() . '-' . basename($_FILES['payment_proof']['name']);
                    $destination = UPLOAD_DIR . $filename;
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $payment_proof_url = 'uploads/proof/' . $filename;
                    }
                }

                if (!$payment_proof_url) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Bukti pembayaran wajib diunggah.']);
                    exit;
                }

                // Update booking dengan info pembayaran
                $sql = "UPDATE bookings SET total_amount = ?, payment_proof_url = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);

                if ($stmt->execute([$total_amount, $payment_proof_url, $booking_id])) {
                    echo json_encode(['status' => 'success', 'message' => 'Pembayaran berhasil dikirim dan menunggu konfirmasi.']);
                } else {
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => 'Gagal mengirim pembayaran.']);
                }
                break;

            case 'update_status':
                if (Session::get('user_role') !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['status' => 'error', 'message' => 'Hanya admin yang bisa mengubah status booking.']);
                    exit;
                }

                $input = json_decode(file_get_contents('php://input'), true);
                $booking_id = isset($input['booking_id']) ? $input['booking_id'] : null;
                $new_status = isset($input['status']) ? $input['status'] : null;

                if (!$booking_id || !in_array($new_status, ['confirmed', 'rejected'])) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Data untuk update status tidak valid.']);
                    exit;
                }

                // Verifikasi bahwa admin berhak mengubah status booking ini (dia adalah pemilik properti)
                $sql = "SELECT b.id FROM bookings b JOIN properties p ON b.property_id = p.id WHERE b.id = ? AND p.admin_id = ?";
                $stmt_check = $pdo->prepare($sql);
                $stmt_check->execute([$booking_id, $user_id]);
                if (!$stmt_check->fetch()) {
                    http_response_code(403);
                    echo json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki izin untuk mengubah status booking ini.']);
                    exit;
                }

                // Update status
                $stmt_update = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                if ($stmt_update->execute([$new_status, $booking_id])) {
                    echo json_encode(['status' => 'success', 'message' => 'Status booking berhasil diperbarui.']);
                } else {
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui status booking.']);
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
