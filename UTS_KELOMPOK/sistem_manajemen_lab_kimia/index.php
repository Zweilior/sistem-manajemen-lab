<?php
session_start();
if (isset($_SESSION['admin'])) {
    header('Location: pages/dashboard.php');
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>STOKLAB – Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
<div class="overlay">
    <div class="twin-box">
        <!-- LEFT BOX : branding -->
        <div class="left-box">
            <p class="nice">nice to see you</p>
            <p class="welcome">WELCOME TO</p>
            <p class="slab">"STOKLAB"</p>
        </div>

        <!-- RIGHT BOX : form -->
        <div class="right-box">
            <img src="assets/img/logo.png" alt="Logo" class="logo-img">
            <h1 class="logo">"STOKLAB"</h1>
            <form action="actions/login.php" method="post" autocomplete="off">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>

                <label class="check">
                    <input type="checkbox" name="remember"> Keep me signed in
                </label>

                <button type="submit">log in</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>