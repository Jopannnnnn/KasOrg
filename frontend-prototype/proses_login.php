<?php
// login_process.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Simulasi pengecekan role login
    if ($email === 'bendahara@organisasi.id') {
        $_SESSION['user_role'] = 'admin';
        $_SESSION['user_name'] = 'Jovan Wijaya';
        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['user_role'] = 'anggota';
        $_SESSION['user_name'] = 'Kent Rafli';
        header("Location: dashboard_anggota.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}