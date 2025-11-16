<?php
session_start();

// 1. Include required files
require_once 'db.php';
require_once 'local_env.php'; 
require_once 'crypto.php';

// 2. Check User Session
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

try {
    // =======================================================
    // PART A: SAVE NEW SERVER (POST Request)
    // =======================================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // --- PERUBAHAN LOGIK DI SINI ---
        // 3. Check Package Limit (Server-side)
        $stmt_count = $conn->prepare("SELECT COUNT(*) FROM cpanel_accounts WHERE user_id = ?");
        $stmt_count->bind_param("i", $user_id);
        $stmt_count->execute();
        $stmt_count->store_result();
        $stmt_count->bind_result($count);
        $stmt_count->fetch();
        $stmt_count->close();
        
        if ($count >= 3) { // Limit changed to 3
            throw new Exception("You have reached the maximum server limit (3) for your plan.");
        }
        // --- TAMAT PERUBAHAN LOGIK ---

        // 4. Get form data
        $display_name = $_POST['display_name'] ?? '';
        $host = $_POST['host'] ?? '';
        $cpanel_user = $_POST['cpanel_user'] ?? '';
        $api_token = $_POST['api_token'] ?? ''; // Plaintext token from form

        // 5. Simple Validation
        if (empty($display_name) || empty($host) || empty($cpanel_user) || empty($api_token)) {
            throw new Exception("All fields are required.");
        }

        // 6. Encrypt the API Token
        $encrypted_token = encrypt_data($api_token);

        // 7. Save to Database (Use Prepared Statements)
        $stmt_insert = $conn->prepare("INSERT INTO cpanel_accounts (user_id, display_name, host, cpanel_user, api_token) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("issss", $user_id, $display_name, $host, $cpanel_user, $encrypted_token);
        
        if (!$stmt_insert->execute()) {
            throw new Exception("Failed to save to database: " . $stmt_insert->error);
        }
        $stmt_insert->close();

        // 8. Redirect with success message
        header("Location: servers.php?success=1");
        exit;
    }

    // =======================================================
    // PART B: DELETE SERVER (GET Request)
    // =======================================================
    elseif (isset($_GET['delete'])) {
        
        $server_id_to_delete = (int)$_GET['delete'];

        // 9. Secure Delete (Prepared Statements)
        // We also check 'user_id' to ensure
        // this user can only delete their own servers.
        $stmt_delete = $conn->prepare("DELETE FROM cpanel_accounts WHERE id = ? AND user_id = ?");
        $stmt_delete->bind_param("ii", $server_id_to_delete, $user_id);
        
        if (!$stmt_delete->execute()) {
            throw new Exception("Failed to delete server: " . $stmt_delete->error);
        }
        $stmt_delete->close();

        // 10. Redirect with 'deleted' message
        header("Location: servers.php?deleted=1");
        exit;
    }

    // =======================================================
    // PART C: Invalid Access
    // =======================================================
    else {
        // If file is accessed directly without POST or GET?delete
        header("Location: servers.php");
        exit;
    }

} catch (Exception $e) {
    // Error Handling: Redirect back with error message
    header("Location: servers.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>
