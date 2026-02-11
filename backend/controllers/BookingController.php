<?php
require_once '../config/db.php';
require_once '../models/BookingModel.php';
require_once '../session.php';
require_once '../helpers/utils.php';

/* ================= ERROR HANDLER ================= */
function handleError($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $errstr,
        'file' => basename($errfile),
        'line' => $errline
    ]);
    exit;
}
set_error_handler("handleError");

/* ================= HEADER ================= */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$action = isset($_GET['action']) ? $_GET['action'] : '';

try {

    /* =====================================================
       CREATE BOOKING
    ===================================================== */
    if ($action === 'create') {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
            exit;
        }

       $id_user = isset($_POST['id_user']) ? $_POST['id_user'] : '';

        $id_property = isset($_POST['id_property']) ? $_POST['id_property'] : '';
$tanggal     = isset($_POST['tanggal']) ? $_POST['tanggal'] : '';


        if (!$id_user || !$id_property || !$tanggal) {
            echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
            exit;
        }

        if ($id_user != getCurrentUserId()) {
            echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
            exit;
        }

        if (strtotime($tanggal) < strtotime(date('Y-m-d'))) {
            echo json_encode(['success' => false, 'message' => 'Tanggal tidak valid']);
            exit;
        }

        $bookingModel = new BookingModel($conn);
        $id = $bookingModel->createBooking($id_user, $id_property, $tanggal);

        echo json_encode([
            'success' => $id ? true : false,
            'message' => $id ? 'Booking berhasil dibuat' : 'Gagal membuat booking',
            'id_booking' => $id
        ]);
        exit;
    }

    /* =====================================================
       GET ALL BOOKING (ADMIN)
    ===================================================== */
    if ($action === 'getAll') {
        requireAdmin();

        $bookingModel = new BookingModel($conn);
        $data = $bookingModel->getAllBookings();

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }

    /* =====================================================
       GET BOOKING BY USER
    ===================================================== */
    if ($action === 'getByUser') {
        requireLogin();

        $id_user = isset($_GET['id_user']) ? $_GET['id_user'] : getCurrentUserId();

        if ($id_user != getCurrentUserId() && getCurrentUserRole() !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
            exit;
        }

        $bookingModel = new BookingModel($conn);
        $data = $bookingModel->getBookingsByUser($id_user);

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }

    /* =====================================================
       GET BOOKING BY ADMIN
    ===================================================== */
    if ($action === 'getByAdmin') {
        requireAdmin();

        $id_admin = isset($_GET['id_admin']) ? $_GET['id_admin'] : getCurrentUserId();

        $bookingModel = new BookingModel($conn);
        $data = $bookingModel->getBookingsByAdmin($id_admin);

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }

    /* =====================================================
       GET BOOKING BY PROPERTY
    ===================================================== */
    if ($action === 'getByProperty') {
        requireLogin();

        $id_property = isset($_GET['id_property']) ? $_GET['id_property'] : '';

        if (!$id_property) {
            echo json_encode(['success' => false, 'message' => 'ID properti wajib']);
            exit;
        }

        $stmt = mysqli_prepare($conn, "SELECT id_admin FROM properties WHERE id_property = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_property);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $property = mysqli_fetch_assoc($result);

        if (!$property) {
            echo json_encode(['success' => false, 'message' => 'Properti tidak ditemukan']);
            exit;
        }

        if ($property['id_admin'] != getCurrentUserId() && getCurrentUserRole() !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
            exit;
        }

        $bookingModel = new BookingModel($conn);
        $data = $bookingModel->getBookingsByProperty($id_property);

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }

    /* =====================================================
       UPDATE STATUS
    ===================================================== */
    if ($action === 'updateStatus') {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
            exit;
        }

        $id_booking = isset($_POST['id_booking']) ? $_POST['id_booking'] : '';
        $status     = isset($_POST['status']) ? $_POST['status'] : '';

        if (!$id_booking || !$status) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
            exit;
        }

        if (!in_array($status, ['pending', 'confirmed', 'cancelled'])) {
            echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
            exit;
        }

        $bookingModel = new BookingModel($conn);
        $result = $bookingModel->updateBookingStatus($id_booking, $status);

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Status berhasil diupdate' : 'Gagal update status'
        ]);
        exit;
    }

    /* =====================================================
       DELETE BOOKING
    ===================================================== */
    if ($action === 'delete') {
        requireAdmin();

        $id = isset($_GET['id']) ? $_GET['id'] : '';

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID booking wajib']);
            exit;
        }

        $bookingModel = new BookingModel($conn);
        $result = $bookingModel->deleteBooking($id);

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Booking dihapus' : 'Gagal menghapus booking'
        ]);
        exit;
    }

    /* =====================================================
       INVALID ACTION
    ===================================================== */
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
