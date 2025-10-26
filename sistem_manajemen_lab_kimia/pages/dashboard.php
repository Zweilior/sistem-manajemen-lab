<?php
session_start();                                               // Mulai session agar bisa cek status login
                                                               // Jika belum login (session 'admin' belum ada), tendang ke halaman login
if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>                                                 <!-- Deklarasi tipe dokumen HTML5 -->
<html lang="en">
<head>                                                          <!-- Mulai tag html dengan bahasa Inggris -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Stok Laboratorium</title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <header class="main-header">                                <!-- Header utama halaman dashboard -->
        <div class="header-left">                               <!-- Bagian kiri header -->                           
            <h2>WELCOME TO STOKLAB</h2>
            <div class="header-title">                          <!-- Judul dan ikon header -->
                <h1>SELECT MENU</h1>
                <img src="../assets/img/logoheaderdashboard.png" alt="Lab Icon">
            </div>
            <hr class="header-line">
        </div>
    
        <div class="header-right">                              <!-- Bagian kanan header -->
            <a href="../actions/logout.php" class="logout-btn">
                <img src="../assets/img/logout.png" alt="Logout">
                <span>LOG OUT</span>
            </a>
        </div>
    </header>

    <main class="menu-container">                                               <!-- Kontainer menu utama dashboard -->                  
        <a href="keloladata/index.php" class="menu-card">                       <!-- Kartu menu kelola data -->
            <img src="../assets/img/keloladata.png" alt="Kelola Data">
            <h3>KELOLA DATA</h3>
        </a>
        <a href="catat_transaksi/index.php" class="menu-card active">           <!-- Kartu menu catat transaksi -->
            <img src="../assets/img/catattransaksi.png" alt="Catat Transaksi">
            <h3>CATAT TRANSAKSI</h3>
        </a>
        <a href="stokbarang/index.php" class="menu-card">                       <!-- Kartu menu stok barang -->
            <img src="../assets/img/stokbarang.png" alt="Stok Barang">
            <h3>STOK BARANG</h3>
        </a>
    </main>
</body>
</html>