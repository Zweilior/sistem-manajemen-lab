<?php
// 1. Informasi koneksi ke MySQL
$host = 'localhost';                                                 // alamat server database (biasanya localhost)
$db   = 'laboratorium';                                              // nama database yang akan dipakai
$user = 'root';                                                      // username MySQL
$pass = '';                                                          // password MySQL (kosongkan jika tidak ada)

// 2. Buat DSN (Data Source Name) untuk PDO
                                                                    // format: "mysql:host=...;dbname=...;charset=..."
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

// 3. Cobalah buat koneksi PDO
try {
                                                                    // Instansiasi PDO: buat objek koneksi dengan DSN, user, pass, plus opsi
    $pdo = new PDO($dsn, $user, $pass, 
                   [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);  // aktifkan exception

// 4. Jika terjadi error, tangkap dan tampilkan pesannya
} catch (PDOException $e) {
                                                                    // Hentikan script dan tampilkan pesan error
    exit("Koneksi gagal: " . $e->getMessage());
}
?>