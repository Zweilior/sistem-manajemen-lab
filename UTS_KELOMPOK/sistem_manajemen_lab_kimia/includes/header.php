<?php
if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title><?= $title ?? 'StokLab' ?></title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<nav class="topbar">
    <span class="brand">STOKLAB</span>
    <a href="../actions/logout.php" class="logout-btn">Logout</a>
</nav>
<main>