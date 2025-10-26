<?php
// --- 1. SETTING ERROR REPORTING ---
ini_set('display_errors',1);                                      // Tampilkan semua pesan error di layar (handy saat development)
error_reporting(E_ALL);                                           // Beritahu PHP untuk melaporkan semua jenis error

// --- 2. MEMULAI SESSION ---
session_start();                                                  // Mulai atau lanjutkan session; agar bisa menyimpan data $_SESSION

// --- 3. MENYAMBUNG KE DATABASE ---
require '../config/koneksi.php';                                  // Masukkan file koneksi PDO ($pdo) agar script ini bisa query DB

// --- 4. MENGAMBIL & MEMBERSIHKAN INPUT ---
$u = trim($_POST['username'] ?? '');                              // Ambil username dari form, hapus spasi depan/belakang
$p = $_POST['password'] ?? '';                                    // Ambil password dari form apa adanya

// --- 5. VALIDASI INPUT KOSONG ---
if (!$u || !$p) {                                                 // Jika salah satu (atau keduanya) masih kosong
    header('Location: ../index.php?err=Isi username & password'); // Redirect ke login + pesan error
    exit;                                                         // Hentikan eksekusi script lebih lanjut
}

// --- 6. QUERY DATA USER ---
$stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?"); // Siapkan statement PDO
$stmt->execute([$u]);                                            // Jalankan query dengan parameter username yg diketik user
$user = $stmt->fetch();                                          // Ambil 1 baris hasil (atau false jika tidak ditemukan)

// --- 7. VERIFIKASI PASSWORD ---
if ($user && password_verify($p, $user['password'])) {           // Jika username ada & password cocok
    $_SESSION['admin'] = $user['username'];                      // Simpan identifier login ke session
    header('Location: ../pages/dashboard.php');                  // Redirect ke halaman dashboard
    exit;                                                        // Hentikan script
}

// --- 8. JIKA LOGIN GAGAL ---
header('Location: ../index.php?err=Username atau password salah'); // Redirect kembali ke login + pesan salah
?>