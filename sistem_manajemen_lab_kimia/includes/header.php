<?php
                                                                    // Jika belum login (session 'admin' belum ada), tendang ke halaman login
if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit;
}
?>
<!doctype html>                                                     <!-- Deklarasi tipe dokumen HTML5 -->
<html lang="id">                                                    <!-- Mulai tag html dengan bahasa Indonesia -->
<head>
    <meta charset="utf-8">                                          <!-- Penentuan karakter UTF-8 untuk mendukung semua simbol -->
    <title><?= $title ?? 'StokLab' ?></title>                       <!-- Judul tab browser, bisa di-override variabel $title -->
    <link rel="stylesheet" href="../assets/css/dashboard.css">      <!-- Link file CSS khusus halaman dashboard -->
</head>
<body>                                                              <!-- Mulai tubuh dokumen -->
<nav class="topbar">                                                <!-- Bar navigasi atas -->
    <span class="brand">STOKLAB</span>                              <!-- Nama/brand aplikasi di kiri nav -->
    <a href="../actions/logout.php" class="logout-btn">Logout</a>   <!-- Tombol logout di kanan nav -->
</nav>
<main>                                                              <!-- Konten utama dimulai (akan ditutup di footer.php) -->   