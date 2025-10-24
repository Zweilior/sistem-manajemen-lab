<?php
require 'config/koneksi.php';   // pastikan $pdo ada

$username = 'admin';
$plainPass = 'rahasia';
$hash = password_hash($plainPass, PASSWORD_DEFAULT);

// hapus user lama (opsional)
$pdo->prepare("DELETE FROM admin WHERE username = ?")->execute([$username]);

// masukkan yang baru
$sql = "INSERT INTO admin (username, password) VALUES (?, ?)";
$pdo->prepare($sql)->execute([$username, $hash]);

echo "Admin berhasil dibuat!<br>";
echo "Username : {$username}<br>";
echo "Hash : {$hash}";