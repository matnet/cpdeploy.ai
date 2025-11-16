<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$servers = [];

// --- Security Update (Use Prepared Statements) ---
$stmt = $conn->prepare("SELECT id, display_name, host, cpanel_user FROM cpanel_accounts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

$stmt->store_result();
$stmt->bind_result($id, $display_name, $host, $cpanel_user);

while ($stmt->fetch()) {
    $servers[] = [
        'id' => $id,
        'display_name' => $display_name,
        'host' => $host,
        'cpanel_user' => $cpanel_user
    ];
}
$stmt->close();
$server_count = count($servers);

// Check package limit (3 for 'free')
$can_add_more = ($server_count < 3); // Limit is 3
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Servers - cpdeploy.ai</title>
    
    <!-- Hax0r Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- Root Variables (from landing_page) --- */
        :root {
            --bg-color: #050505;
            --container-bg: #1a1a1a;
            --input-bg: #2a2a2a;
            --border-color: #333;
            --text-main: #ffffff;
            --text-muted: #a1a1aa;
            --gold: #D4AF37;
            --orange: #FF8C00;
            --gradient-gold: linear-gradient(135deg, var(--gold), var(--orange));
        }

        body {
            font-family: 'IBM Plex Mono', monospace;
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            padding: 40px 20px;
            margin: 0;
            min-height: 100vh;
        }
        .container {
            background-color: var(--container-bg);
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 680px; 
            border: 1px solid var(--border-color);
        }
        
        /* Logo (from index.php) */
        .logo {
            font-family: 'Chakra Petch', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -1px;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .logo span { color: var(--orange); }
        
        h3 {
            color: var(--text-main);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
            margin-top: 2rem;
            font-family: 'Chakra Petch', sans-serif;
            font-weight: 600;
            text-transform: uppercase;
        }
        a { color: var(--gold); text-decoration: none; font-weight: 600;}
        a:hover { text-decoration: underline; }
        p { color: var(--text-muted); }
        
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 500; color: var(--text-muted); }
        .form-group input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            font-size: 16px;
            font-family: 'IBM Plex Mono', monospace;
            background-color: var(--input-bg);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-sizing: border-box; 
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-group input[type="text"]:focus {
            border-color: var(--gold); 
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.25);
            outline: none;
            background-color: #333;
        }
        
        /* Button (Gold/Orange Theme) */
        button {
            display: inline-block;
            padding: 14px 20px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            text-align: center;
            font-family: 'Chakra Petch', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            
            background: var(--gradient-gold);
            color: #000;
            border: none;
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.3);
            
            width: 100%;
            transition: 0.3s;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 140, 0, 0.5);
        }
        button:disabled {
            background: #333;
            color: #888;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .server-list { margin-top: 2rem; }
        .server-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem; 
            background-color: var(--input-bg); 
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .server-item span { font-weight: 500; color: var(--text-main); }
        .delete-btn { color: #ff4d4d; text-decoration: none; font-weight: 500; }
        
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid; }
        .alert-info { background-color: #2d3b4f; color: #b8d6f3; border-color: #b8d6f3; }
        .alert-success { background-color: #2a4832; color: #a3d9b1; border-color: #a3d9b1; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo Teks (menggantikan H1) -->
        <div class="logo">cpdeploy<span>.ai</span></div>
        <p style="text-align: center; margin-top: -1.5rem; margin-bottom: 2rem;">
            <a href="index.php">&larr; Back to Main Console</a>
        </p>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Server saved successfully!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Server deleted successfully.</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert" style="background-color: #721c24; color: #f8d7da; border-color: #f5c6cb;">
                <strong>Error:</strong> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (!$can_add_more): ?>
            <div class="alert alert-info">
                You are on the Free Plan (3 server limit). Remove an existing server to add a new one.
            </div>
        <?php endif; ?>

        <form action="save_server.php" method="POST" <?php if (!$can_add_more) echo 'style="display:none;"'; ?>>
            <h3>Add New Server</h3>
            <div class="form-group">
                <label for="display_name">Display Name (e.g., "Client A Server")</label>
                <input type="text" id="display_name" name="display_name" required>
            </div>
            <div class="form-group">
                <label for="host">cPanel Host (e.g., yourdomain.com)</label>
                <input type="text" id="host" name="host" required>
            </div>
            <div class="form-group">
                <label for="cpanel_user">cPanel Username</label>
                <input type="text" id="cpanel_user" name="cpanel_user" required>
            </div>
            <div class="form-group">
                <label for="api_token">cPanel API Token</label>
                <input type="text" id="api_token" name="api_token" required placeholder="Token will be encrypted upon saving">
            </div>
            <button type="submit" <?php if (!$can_add_more) echo 'disabled'; ?>>Save Server</button>
        </form>

        <div class="server-list">
            <h3>Your Servers</h3>
            <?php if (empty($servers)): ?>
                <p>No servers found.</p>
            <?php else: ?>
                <?php foreach ($servers as $server): ?>
                <div class="server-item">
                    <span><?php echo htmlspecialchars($server['display_name']); ?></span>
                    <a href="save_server.php?delete=<?php echo $server['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</a>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>