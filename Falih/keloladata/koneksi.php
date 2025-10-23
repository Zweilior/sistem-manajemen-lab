<?php

// 1. Definisikan parameter koneksi
$db_host = "localhost";      
$db_user = "root";           
$db_pass = "";               
$db_name = "laboratorium"; // <-- NAMA DATABASE SUDAH DISESUAIKAN

// 2. Buat koneksi
$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// 3. Cek koneksi
if (!$koneksi) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}
?>