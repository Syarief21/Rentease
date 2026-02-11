<?php
require_once '../config/db.php';
require_once '../models/PropertyModel.php';
require_once '../session.php';
require_once '../helpers/utils.php';

Session::start();

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

try {

    /* ===================== CREATE PROPERTY ===================== */
    if ($action === 'create') {

        requireAdmin(); // pastikan admin login

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
            exit;
        }

        // SESUAIKAN DENGAN FORM
        $name        = isset($_POST['name']) ? trim($_POST['name']) : '';
        $location    = isset($_POST['location']) ? trim($_POST['location']) : '';
        $price       = isset($_POST['price']) ? $_POST['price'] : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $id_admin    = getCurrentUserId();

        if (empty($name) || empty($location) || empty($price) || empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
            exit;
        }

        if (!is_numeric($price) || $price <= 0) {
            echo json_encode(['success' => false, 'message' => 'Harga tidak valid']);
            exit;
        }

        /* ===================== UPLOAD IMAGE ===================== */
        $image = 'default.jpg';

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/property/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $uploaded = uploadFile($_FILES['image'], $uploadDir);
            if ($uploaded) {
                $image = $uploaded;
            }
        }

        /* ===================== SAVE TO DB ===================== */
        $propertyModel = new PropertyModel($conn);

        $result = $propertyModel->createProperty(
            $name,
            $location,
            $price,
            $description,
            $image,
            $id_admin
        );

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Kos berhasil ditambahkan'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menyimpan data'
            ]);
        }

        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
