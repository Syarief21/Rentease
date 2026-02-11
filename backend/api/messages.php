<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../session.php';

Session::start();

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if (!Session::isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Silakan login terlebih dahulu'
    ]);
    exit;
}

$user_id = Session::get('user_id');

/* ===================== POST ===================== */
if ($method === 'POST' && $action === 'reply') {

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        echo json_encode([
            'success' => false,
            'message' => 'Data tidak valid'
        ]);
        exit;
    }

    $receiver_id = isset($data['receiver_id']) ? $data['receiver_id'] : null;
$message     = isset($data['message']) ? trim($data['message']) : '';

    if (!$receiver_id || $message === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Receiver dan pesan wajib diisi'
        ]);
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO messages (sender_id, receiver_id, message_content)
             VALUES (?, ?, ?)"
        );
        $stmt->execute([$user_id, $receiver_id, $message]);

        echo json_encode([
            'success' => true
        ]);
        exit;

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'DB error'
        ]);
        exit;
    }
}

/* ===================== DEFAULT ===================== */
echo json_encode([
    'success' => false,
    'message' => 'Endpoint tidak valid'
]);
