<?php
session_start();                                // Memulai session agar bisa cek status login
                                                // Jika sudah login (session 'admin' ada), langsung ke halaman dashboard
if (isset($_SESSION['admin'])) {
    header('Location: pages/dashboard.php');
    exit;
}
?>
<!doctype html>                                                       <!-- Deklarasi tipe dokumen HTML5 -->
<html lang="id">
<head>                                                                <!-- Mulai tag html dengan bahasa Indonesia -->
    <meta charset="utf-8">
    <title>STOKLAB – Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/login.css">               <!-- Link file CSS khusus halaman login -->
</head>
<body>
<div class="overlay">                                                 <!-- Overlay utama halaman login -->
    <div class="twin-box">                                            <!-- Kontainer kotak ganda (kiri & kanan) -->
        <!-- LEFT BOX : branding -->
        <div class="left-box">                                        <!-- Kotak kiri untuk branding -->   
            <p class="nice">nice to see you</p>
            <p class="welcome">WELCOME TO</p>
            <p class="slab">"STOKLAB"</p>
        </div>

        <!-- RIGHT BOX : form -->
        <div class="right-box">                                      <!-- Kotak kanan untuk form login -->                  
            <img src="assets/img/logo.png" alt="Logo" class="logo-img">
            <h1 class="logo">"STOKLAB"</h1>                          <!-- Judul/logo aplikasi -->
            <form action="actions/login.php" method="post" autocomplete="off">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>

                <button type="submit">log in</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>