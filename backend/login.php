<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email dan password harus diisi!";
        header("Location: ../views/auth/login.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT id_user, nama, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['error'] = "Email tidak ditemukan!";
        header("Location: ../views/auth/login.php");
        exit();
    }

    $stmt->bind_result($id_user, $nama, $hashPassword, $role);
    $stmt->fetch();

    if (password_verify($password, $hashPassword)) {
        $_SESSION['user_id'] = $id_user;
        $_SESSION['nama'] = $nama;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;

        if ($role === 'admin') {
            header("Location: ../views/admin/dashboard.php");
        } else {
            header("Location: ../views/homepage.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Password salah!";
        header("Location: ../views/auth/login.php");
        exit();
    }

    $stmt->close();
}
$conn->close();
