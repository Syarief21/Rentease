<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../session.php';

Session::start();

/* ================== AUTH ================== */
function require_admin() {
    $role = Session::get('user_role');
    if (!$role || $role !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Akses ditolak. Login sebagai admin.'
        ]);
        exit;
    }
}

/* ================== UPLOAD ================== */
define('UPLOAD_DIR', __DIR__ . '/../../uploads/property/');

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? $_GET['id'] : null;

/* ================== ROUTER ================== */
switch ($method) {

    /* ================= GET ================= */
    case 'GET':
        if ($id) {
            $stmt = $pdo->prepare(
                "SELECT p.*, u.name AS admin_name
                 FROM properties p
                 JOIN users u ON p.admin_id = u.id
                 WHERE p.id = ?"
            );
            $stmt->execute([$id]);
            $data = $stmt->fetch();

            if ($data) {
                echo json_encode(['status' => 'success', 'data' => $data]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Properti tidak ditemukan']);
            }
            exit;
        }

        $role = Session::get('user_role');

        if ($role === 'admin') {
            $admin_id = Session::get('user_id');
            $stmt = $pdo->prepare("SELECT * FROM properties WHERE admin_id = ? ORDER BY id DESC");
            $stmt->execute([$admin_id]);
        } else {
            $stmt = $pdo->query("SELECT * FROM properties ORDER BY id DESC");
        }

        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
        break;

    /* ================= POST (CREATE / UPDATE) ================= */
    case 'POST':
        require_admin();

        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
$location = isset($_POST['location']) ? trim($_POST['location']) : '';
$price = isset($_POST['price']) ? $_POST['price'] : '';
$total_rooms = isset($_POST['total_rooms']) ? $_POST['total_rooms'] : '';
$available_rooms = isset($_POST['available_rooms']) ? $_POST['available_rooms'] : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$update_id = isset($_POST['id']) ? $_POST['id'] : null;


        if (
            $name === '' ||
            $location === '' ||
            $price === '' ||
            $total_rooms === '' ||
            $available_rooms === ''
        ) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi']);
            exit;
        }

        $admin_id = Session::get('user_id');
        $image_url = null;

        /* ===== Upload Image ===== */
        if (!empty($_FILES['image']['name'])) {
            $filename = uniqid() . '_' . basename($_FILES['image']['name']);
            $target = UPLOAD_DIR . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $image_url = 'uploads/property/' . $filename;
            }
        }

        /* ===== UPDATE ===== */
        if ($update_id) {
            $stmt = $pdo->prepare("SELECT image_url FROM properties WHERE id=? AND admin_id=?");
            $stmt->execute([$update_id, $admin_id]);
            $old = $stmt->fetch();

            if (!$old) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Tidak diizinkan']);
                exit;
            }

            if (!$image_url) {
                $image_url = $old['image_url'];
            }

            $sql = "UPDATE properties
                    SET name=?, location=?, price=?, total_rooms=?, available_rooms=?, description=?, image_url=?
                    WHERE id=? AND admin_id=?";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([
                $name, $location, $price,
                $total_rooms, $available_rooms,
                $description, $image_url,
                $update_id, $admin_id
            ])) {
                echo json_encode(['status' => 'success', 'message' => 'Properti diperbarui']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal update']);
            }

            exit;
        }

        /* ===== CREATE ===== */
        if (!$image_url) {
            $image_url = 'uploads/property/default.jpg';
        }

        $sql = "INSERT INTO properties
                (admin_id, name, location, price, total_rooms, available_rooms, description, image_url)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([
            $admin_id,
            $name,
            $location,
            $price,
            $total_rooms,
            $available_rooms,
            $description,
            $image_url
        ])) {
            echo json_encode(['status' => 'success', 'message' => 'Kos berhasil ditambahkan']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Insert gagal',
                'sql_error' => $stmt->errorInfo()
            ]);
        }
        break;

    /* ================= DELETE ================= */
    case 'DELETE':
        require_admin();

        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID diperlukan']);
            exit;
        }

        $admin_id = Session::get('user_id');

        $stmt = $pdo->prepare("SELECT image_url FROM properties WHERE id=? AND admin_id=?");
        $stmt->execute([$id, $admin_id]);
        $data = $stmt->fetch();

        if (!$data) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Tidak diizinkan']);
            exit;
        }

        if ($data['image_url'] && file_exists(__DIR__ . '/../../' . $data['image_url'])) {
            unlink(__DIR__ . '/../../' . $data['image_url']);
        }

        $stmt = $pdo->prepare("DELETE FROM properties WHERE id=?");
        $stmt->execute([$id]);

        echo json_encode(['status' => 'success', 'message' => 'Properti dihapus']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method tidak didukung']);
}
