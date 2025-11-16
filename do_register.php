<?php
session_start();
require_once 'db.php'; // Sertakan fail sambungan DB anda

// Hanya benarkan kaedah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

// 1. Dapatkan data borang
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$invitation_code = $_POST['invitation_code'] ?? '';

// 2. Validasi Asas
if (empty($username) || empty($password) || empty($confirm_password) || empty($invitation_code)) {
    header('Location: register.php?error=All fields are required.');
    exit;
}

if ($password !== $confirm_password) {
    header('Location: register.php?error=Passwords do not match.');
    exit;
}

// 3. Logik Pendaftaran (Menggunakan Transaksi)
$conn->autocommit(FALSE); // Mulakan mod transaksi
$conn->begin_transaction();

try {
    // 3a. Semak Kod Jemputan (Kunci baris untuk elak race condition)
    $stmt_code = $conn->prepare("SELECT id FROM invitation_codes WHERE code = ? AND is_used = 0 FOR UPDATE");
    $stmt_code->bind_param("s", $invitation_code);
    $stmt_code->execute();
    $result_code = $stmt_code->get_result();
    
    if ($result_code->num_rows === 0) {
        throw new Exception("Invalid or already used invitation code.");
    }
    $stmt_code->close();

    // 3b. Semak jika Nama Pengguna (username) telah wujud
    $stmt_user = $conn->prepare("SELECT id FROM laksana_users WHERE username = ?");
    $stmt_user->bind_param("s", $username);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    
    if ($result_user->num_rows > 0) {
        throw new Exception("Username already exists. Please choose another.");
    }
    $stmt_user->close();

    // 3c. Hash kata laluan
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    if ($password_hash === false) {
        throw new Exception("Failed to hash password.");
    }

    // 3d. Cipta pengguna baru
    $stmt_create = $conn->prepare("INSERT INTO laksana_users (username, password_hash) VALUES (?, ?)");
    $stmt_create->bind_param("ss", $username, $password_hash);
    if (!$stmt_create->execute()) {
        throw new Exception("Failed to create user account.");
    }
    $new_user_id = $conn->insert_id; // Dapatkan ID pengguna baru
    $stmt_create->close();

    // 3e. Tandakan kod jemputan sebagai telah digunakan
    $stmt_update = $conn->prepare("UPDATE invitation_codes SET is_used = 1, used_by_user_id = ? WHERE code = ?");
    $stmt_update->bind_param("is", $new_user_id, $invitation_code);
    if (!$stmt_update->execute()) {
        throw new Exception("Failed to update invitation code status.");
    }
    $stmt_update->close();

    // 4. Jika semua berjaya, commit transaksi
    $conn->commit();
    header('Location: login.php?success=1'); // Hantar ke login dengan mesej kejayaan
    exit;

} catch (Exception $e) {
    // 5. Jika berlaku ralat, rollback semua perubahan
    $conn->rollback();
    header('Location: register.php?error=' . urlencode($e->getMessage()));
    exit;
} finally {
    $conn->autocommit(TRUE); // Kembalikan ke mod autocommit
}
?>
