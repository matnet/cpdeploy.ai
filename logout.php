<?php
// logout.php (VERSI BARU)
session_start();

// Musnahkan semua data sesi
$_SESSION = array();
session_destroy();

// Halakan kembali ke halaman utama (landing page)
header("Location: landing_page.php");
exit;
?>