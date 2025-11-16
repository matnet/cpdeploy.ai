<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cpdeploy.ai - Enterprise cPanel AI Agent</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- VARIABLES & RESET --- */
        :root {
            --bg-color: #050505;
            --card-bg: #111111;
            --text-main: #ffffff;
            --text-muted: #a1a1aa;
            
            /* THEME COLORS: Black, Gold, Orange */
            --gold: #D4AF37;
            --gold-light: #F2D06B;
            --orange: #FF8C00;
            --orange-dark: #CC7000;
            
            --gradient-gold: linear-gradient(135deg, var(--gold), var(--orange));
            --gradient-dark: linear-gradient(180deg, #1a1a1a 0%, #050505 100%);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'IBM Plex Mono', monospace;
            background-color: var(--bg-color);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
        }

        a { text-decoration: none; transition: 0.3s; }
        ul { list-style: none; }

        /* --- UTILITIES --- */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }
        .text-gold { color: var(--gold); }
        .text-gradient {
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .btn {
            display: inline-block;
            padding: 12px 32px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            text-align: center;
            font-family: 'Chakra Petch', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .btn-primary {
            background: var(--gradient-gold);
            color: #000;
            border: none;
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 140, 0, 0.5);
        }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--gold);
            color: var(--gold);
        }
        .btn-outline:hover {
            background: rgba(212, 175, 55, 0.1);
            color: var(--gold-light);
        }

        /* --- HEADER --- */
        header {
            padding: 1.5rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
            background: rgba(5, 5, 5, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-family: 'Chakra Petch', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -1px;
        }
        .logo span { color: var(--orange); }
        
        .nav-links { display: flex; gap: 2rem; align-items: center; }
        .nav-links a { 
            color: var(--text-muted); 
            font-size: 1rem;
            font-family: 'Chakra Petch', sans-serif;
            text-transform: uppercase;
            font-weight: 600;
        }
        .nav-links a:hover { color: var(--gold); }

        /* --- HERO SECTION --- */
        .hero {
            padding: 160px 0 100px;
            text-align: center;
            position: relative;
            background: radial-gradient(circle at 50% 20%, rgba(255, 140, 0, 0.15) 0%, rgba(0, 0, 0, 0) 50%);
        }
        .hero h1 {
            font-family: 'Chakra Petch', sans-serif;
            font-size: 4rem;
            line-height: 1.1;
            font-weight: 800;
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
        }
        .hero p {
            font-size: 1.1rem;
            color: var(--text-muted);
            max-width: 700px;
            margin: 0 auto 2.5rem;
        }
        .hero-btns {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 4rem;
        }

        /* 3D Dashboard Effect (Like PDF) */
        .dashboard-preview {
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
            perspective: 1000px;
        }
        .dashboard-img {
            width: 100%;
            border-radius: 12px;
            background: #1a1a1a;
            border: 1px solid rgba(212, 175, 55, 0.3);
            box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 30px rgba(255, 140, 0, 0.1);
            transform: rotateX(10deg) scale(0.95); /* The tilt effect */
            transition: transform 0.5s ease;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .dashboard-img:hover {
            transform: rotateX(0deg) scale(1);
        }
        .mock-header { height: 40px; background: #222; border-bottom: 1px solid #333; display: flex; align-items: center; padding: 0 1rem; gap: 8px; }
        .dot { width: 10px; height: 10px; border-radius: 50%; background: #444; }
        .mock-body { padding: 2rem; min-height: 400px; display: grid; grid-template-columns: 250px 1fr; gap: 2rem; }
        .mock-sidebar { background: #111; border-radius: 8px; height: 100%; }
        .mock-content { 
            background: #000; 
            border-radius: 8px; 
            border: 1px solid #333; 
            position: relative; 
            padding: 2rem; 
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .mock-terminal-window { 
            color: var(--gold); 
            font-family: 'IBM Plex Mono', monospace; 
            font-size: 0.75rem; 
            text-align: left; 
            line-height: 1.4; 
        }
        
        .mock-terminal-window pre {
            color: var(--gold); 
            text-align: center;
            font-family: 'IBM Plex Mono', monospace; 
            white-space: pre;
            margin: 0;
            padding: 0;
            font-size: 0.9em; 
        }
        
        .mock-terminal-text {
            margin-top: 1.5rem; 
            text-align: left; 
            font-size: 0.8rem;
            line-height: 1.6;
        }
        .mock-terminal-text .ok { color: #27c93f; } /* Hijau */
        .mock-terminal-text .info { color: #6f42c1; } /* Ungu */
        .mock-terminal-text .prompt { color: #007bff; } /* Biru */
        .mock-terminal-text .cursor { 
            display: inline-block; 
            width: 8px; 
            height: 1.1em; 
            background: white; 
            animation: blink 1s step-end infinite;
            margin-left: 4px;
            vertical-align: bottom;
        }
        @keyframes blink { 
            from, to { background: white; } 
            50% { background: transparent; } 
        }

        /* --- FEATURES --- */
        .features { padding: 100px 0; background-color: #0a0a0a; }
        .section-title { text-align: center; margin-bottom: 4rem; }
        .section-title h2 { 
            font-family: 'Chakra Petch', sans-serif;
            font-size: 2.5rem; 
            margin-bottom: 1rem; 
        }
        .section-title p { color: var(--text-muted); }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        .feature-card {
            background: linear-gradient(180deg, #111 0%, #080808 100%);
            padding: 2rem;
            border-radius: 12px;
            border: 1px solid #222;
            transition: 0.3s;
        }
        .feature-card:hover {
            border-color: var(--orange);
            transform: translateY(-5px);
        }
        .icon-box {
            width: 50px;
            height: 50px;
            background: rgba(212, 175, 55, 0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: var(--gold);
            font-size: 1.5rem;
        }
        .feature-card h3 { 
            font-family: 'Chakra Petch', sans-serif;
            text-transform: uppercase;
            margin-bottom: 1rem; 
            font-size: 1.25rem; 
        }
        .feature-card p { color: var(--text-muted); font-size: 0.95rem; }


        /* --- BAHAGIAN PRICING TELAH DIBUANG --- */


        /* --- FOOTER --- */
        footer {
            border-top: 1px solid #222;
            padding: 4rem 0 2rem;
            background: #050505;
            text-align: center;
        }
        .footer-links { display: flex; justify-content: center; gap: 2rem; margin-bottom: 2rem; }
        .footer-links a { 
            color: var(--text-muted); 
            font-size: 0.9rem; 
            font-family: 'Chakra Petch', sans-serif;
            text-transform: uppercase;
        }
        .footer-links a:hover { color: var(--gold); }
        .copyright { color: #555; font-size: 0.85rem; }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 { font-size: 2.5rem; }
            .nav-links { display: none; } 
        }
    </style>
</head>
<body>

    <header>
        <div class="container">
            <nav>
                <div class="logo">cpdeploy<span>.ai</span></div>
                <ul class="nav-links">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#github-repo">GitHub</a></li> 
                    <li><a href="login.php">Login</a></li>
                    <li><a href="#github-repo" class="btn btn-primary" style="padding: 8px 20px; color:black;">View on GitHub</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <h1>
                Deploy <span class="text-gradient">Smarter</span>,<br>
                Not Harder.
            </h1>
            <p>
                The Enterprise AI Agent for instant cPanel automation.
                Transform plain prompts into live applications on your own server.
            </p>
            <div class="hero-btns">
                <a href="#github-repo" class="btn btn-primary">View on GitHub</a>
                <a href="#features" class="btn btn-outline">See Features</a>
            </div>

            <div class="dashboard-preview">
                <div class="dashboard-img">
                    <div class="mock-header">
                        <div class="dot" style="background:#ff5f56"></div>
                        <div class="dot" style="background:#ffbd2e"></div>
                        <div class="dot" style="background:#27c93f"></div>
                    </div>
                    <div class="mock-body">
                        <div class="mock-sidebar"></div>
                        <div class="mock-content">
                            <div class="mock-terminal-window">
<pre style="color: var(--gold); text-align: center;">
  ██████╗  ██████╗  ██████╗  ███████╗ ██████╗  ██╗      █████╗  ██╗   ██╗
 ██╔════╝  ██╔══██╗ ██╔══██╗ ██╔════╝ ██╔══██╗ ██║     ██╔══██╗ ╚██╗ ██╔╝
 ██║       ██████╔╝ ██║  ██║ █████╗   ██████╔╝ ██║     ██║  ██║  ╚████╔╝ 
 ██║       ██╔═══╝  ██║  ██║ ██╔══╝   ██╔═══╝  ██║     ██║  ██║   ╚██╔╝  
 ╚██████╗  ██║      ██████╔╝ ███████╗ ██║      ███████╗╚█████╔╝    ██║   
  ╚═════╝  ╚═╝      ╚═════╝  ╚══════╝ ╚═╝      ╚══════╝ ╚════╝     ╚═╝   
</pre>
                                <div class="mock-terminal-text">
                                    <span class="ok">[OK]</span> Sambungan ke cpdeploy... berjaya.
                                    <br>
                                    <span class="info">[INFO]</span> Perkhidmatan tersedia: AI cPanel Deployer
                                    <br>
                                    <span class="info">[INFO]</span> Versi: 1.4 (SaaS)
                                    <br><br>
                                    <span class="prompt">cpdeploy:~$</span> Create Simple Faraid Calculator<span class="cursor"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <div class="section-title">
                <h2>Built for <span class="text-gold">Enterprise</span> Complexity</h2>
                <p>Low-code approach, tailored logic, and deep integration with cPanel.</p>
            </div>

            <div class="feature-grid">
                <div class="feature-card">
                    <div class="icon-box"><i class="fa-solid fa-brain"></i></div>
                    <h3>AI Architect</h3>
                    <p>GPT-4o generates complex, multi-step deployment plans from a single prompt. It thinks before it acts.</p>
                </div>
                <div class="feature-card">
                    <div class="icon-box"><i class="fa-solid fa-shield-halved"></i></div>
                    <h3>Secure Executor</h3>
                    <p>Your API tokens are encrypted with AES-256-GCM. Credentials are decrypted only at the moment of execution.</p>
                </div>
                <div class="feature-card">
                    <div class="icon-box"><i class="fa-solid fa-server"></i></div>
                    <h3>Multi-Server Manager</h3>
                    <p>Manage 1 or 50 servers from a single dashboard. Deploy to Client A and Client B without switching tabs.</p>
                </div>
                <div class="feature-card">
                    <div class="icon-box"><i class="fa-solid fa-database"></i></div>
                    <h3>Full Automation</h3>
                    <p>We don't just upload files. We create Databases, Users, Privileges, Subdomains, and Email Accounts.</p>
                </div>
                <div class="feature-card">
                    <div class="icon-box"><i class="fa-solid fa-terminal"></i></div>
                    <h3>Terminal UI</h3>
                    <p>A professional, dark-themed terminal interface that developers feel at home with.</p>
                </div>
                <div class="feature-card">
                    <div class="icon-box"><i class="fa-solid fa-bolt"></i></div>
                    <h3>Fastest Production</h3>
                    <p>Connect directly into cPanel applications with schema-validated outputs. No middleware required.</p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="logo" style="font-size: 1.2rem; margin-bottom: 1rem;">cpdeploy<span>.ai</span></div>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Support</a>
                <a href="#">Contact</a>
            </div>
            <p class="copyright">Copyright &copy; <?php echo date("Y"); ?> M A T N E T - H O S T. All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>
