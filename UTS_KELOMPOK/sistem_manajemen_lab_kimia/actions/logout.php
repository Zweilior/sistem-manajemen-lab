<?php
session_start();
session_unset();     // hapus semua variabel session
session_destroy();   // hancurkan session-nya

// redirect ke halaman login
header('Location: ../index.php');
exit;
?>