<?php
// login.php
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
    <title>Login - cpdeploy.ai</title>
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
            /* Guna fon 'IBM Plex Mono' */
            font-family: 'IBM Plex Mono', monospace;
            background-color: #1e1e1e; /* Latar belakang gelap */
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
            max-width: 480px; /* Saiz kotak borang yang sesuai */
            border: 1px solid #444; 
        }
        
        /* --- MULA: Logo dari landing_page.php --- */
        .logo {
            font-family: 'Chakra Petch', sans-serif;
            font-size: 2.5rem; /* Besarkan logo */
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -1px;
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo span { color: var(--orange); }
        /* --- TAMAT: Logo --- */
        
        /* Form Styling */
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
        
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            font-size: 16px;
            /* Guna fon 'IBM Plex Mono' untuk input */
            font-family: 'IBM Plex Mono', monospace;
            background-color: #3a3a3a;
            color: #f8f9fa;
            border: 1px solid #555;
            border-radius: 8px;
            box-sizing: border-box; 
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        
        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
            /* Guna 'focus' Emas/Oren */
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.25);
            outline: none;
            background-color: #404040; 
        }
        
        /* Guna gaya butang dari landing_page.php */
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
            
            /* Warna Emas/Oren */
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
        
        /* Mesej 'Success' (selepas mendaftar) */
        .alert-success {
            background-color: #2a4832; 
            color: #a3d9b1; 
            border: 1px solid #a3d9b1;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 500;
        }

        /* Pautan ke Register */
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9em;
            color: #adb5bd;
        }
        .login-link a {
            color: #007bff; /* Kekalkan biru untuk kontras */
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
        
        <!-- Logo Teks dari landing_page.php -->
        <div class="logo">cpdeploy<span>.ai</span></div>

        <form action="check_login.php" method="POST">
            <?php if (isset($_GET['error'])): ?>
                <p class="error">Invalid username or password!</p>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert-success">
                    Registration successful! You can now log in.
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Login</button>
        </form>

        <!-- Pautan baru ke Register -->
        <div class="login-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>
