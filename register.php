<?php
// register.php
session_start();
// Redirect if already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - cpdeploy.ai</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Fon "Hax0r" dari landing_page.php -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        /* Menggunakan pembolehubah (variables) dari landing_page.php */
        :root {
            --bg-color: #050505;
            --text-main: #ffffff;
            --text-muted: #a1a1aa;
            --gold: #D4AF37;
            --orange: #FF8C00;
            --gradient-gold: linear-gradient(135deg, var(--gold), var(--orange));
        }

        body {
            font-family: 'IBM Plex Mono', monospace;
            background-color: #1e1e1e; 
            color: #f8f9fa; 
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden; 
        }
        
        .login-container {
            background-color: #2d2d2d; 
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3); 
            width: 100%;
            max-width: 480px; 
            border: 1px solid #444; 
        }
        
        .logo {
            font-family: 'Chakra Petch', sans-serif;
            font-size: 2.5rem; 
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -1px;
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo span { color: var(--orange); }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #adb5bd; 
            font-size: 0.9rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            font-size: 16px;
            font-family: 'IBM Plex Mono', monospace;
            background-color: #3a3a3a;
            color: #f8f9fa;
            border: 1px solid #555;
            border-radius: 8px;
            box-sizing: border-box; 
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        
        .form-group input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.25);
            outline: none;
            background-color: #404040; 
        }
        
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
        
        .error {
            background-color: #721c24; 
            color: #f8d7da; 
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 500;
            border: 1px solid #f5c6cb;
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9em;
            color: #adb5bd;
        }
        .login-link a {
            color: #007bff; 
            font-weight: 500;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        
        <div class="logo">cpdeploy<span>.ai</span></div>

        <form action="do_register.php" method="POST">
            <?php if (isset($_GET['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <!-- --- MEDAN BARU DI SINI --- -->
            <div class="form-group">
                <label for="invitation_code">Invitation Code:</label>
                <input type="text" id="invitation_code" name="invitation_code" required>
            </div>
            <!-- --- TAMAT MEDAN BARU --- -->
            
            <button type="submit">Create Account</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
        
    </div>
</body>
</html>
