<?php
// --- 1. MEMULAI SESSION ---
session_start();                   // Pastikan session aktif agar bisa dimanipulasi

// --- 2. MENGHAPUS SEMUA DATA SESSION ---
session_unset();                   // Kosongkan / hapus semua variabel session yang tersimpan

// --- 3. MENGHANCURKAN SESSION ITU SENDIRI ---
session_destroy();                 // Hancurkan file/session di server (opsional tapi direkomendasikan)

// --- 4. REDIRECT KE HALAMAN LOGIN ---
header('Location: ../index.php');  // Alihkan pengguna kembali ke halaman login
exit;                              // Hentikan eksekusi script lebih lanjut
?>