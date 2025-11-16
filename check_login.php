<?php
// check_login.php (VERSI BARU)
session_start();
require_once 'db.php'; // Sambung ke database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        // --- PERUBAHAN DI SINI ---
        // Ikat (bind) hasil ke pembolehubah
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Berjaya! Cipta sesi
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $user_id; // <-- PENTING! Simpan ID pengguna
            
            header("Location: index.php");
            exit;
        }
    }
    
    header("Location: login.php?error=1");
    exit;
} else {
    header("Location: login.php");
    exit;
}
?>