<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
session_start();
require '../config/koneksi.php';

$u = trim($_POST['username'] ?? '');      // <— HAPUS strtolower
$p = $_POST['password']   ?? '';

if (!$u || !$p) {
    header('Location: ../index.php?err=Isi username & password');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
$stmt->execute([$u]);
$user = $stmt->fetch();

/* ========================================== */

if ($user && password_verify($p, $user['password'])) {
    $_SESSION['admin'] = $user['username'];
    header('Location: ../pages/dashboard.php');
    exit;
}

header('Location: ../index.php?err=Username atau password salah');
?>