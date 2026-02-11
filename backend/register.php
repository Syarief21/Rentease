<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim(isset($_POST['nama']) ? $_POST['nama'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (empty($nama) || empty($email) || empty($password) || empty($confirm)) {
        $_SESSION['error'] = "Semua kolom harus diisi!";
        header("Location: ../views/auth/register.php");
        exit();
    }

    if ($password !== $confirm) {
        $_SESSION['error'] = "Password dan konfirmasi tidak sama!";
        header("Location: ../views/auth/register.php");
        exit();
    }

    // Cek email unik
    $cek = $conn->prepare("SELECT id_user FROM users WHERE email = ?");
    $cek->bind_param("s", $email);
    $cek->execute();
    $cek->store_result();
    if ($cek->num_rows > 0) {
        $_SESSION['error'] = "Email sudah terdaftar!";
        $cek->close();
        header("Location: ../views/auth/register.php");
        exit();
    }
    $cek->close();

    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nama, $email, $hashPassword);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
        header("Location: ../views/auth/login.php");
        exit();
    } else {
        $_SESSION['error'] = "Terjadi kesalahan server: " . $stmt->error;
        header("Location: ../views/auth/register.php");
        exit();
    }

    $stmt->close();
}
$conn->close();
