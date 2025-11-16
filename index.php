<?php
session_start();
require_once 'db.php'; // Required for $conn

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user's server list for the dropdown
$user_id = $_SESSION['user_id'];
$servers = [];

// --- Security Update: Use Prepared Statements ---
$stmt = $conn->prepare("SELECT id, display_name FROM cpanel_accounts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result(); // Use get_result if available
while ($row = $result->fetch_assoc()) {
    $servers[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Console - cpdeploy.ai</title>
    
    <!-- Hax0r Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <style>
        /* --- Root Variables (from landing_page) --- */
        :root {
            --bg-color: #050505;
            --container-bg: #1a1a1a; /* Darker than login */
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
            align-items: flex-start; /* Align top */
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        /* Main Terminal Window */
        .terminal-window {
            width: 100%;
            max-width: 900px;
            background-color: var(--container-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            padding: 25px;
            box-sizing: border-box;
        }
        
        /* Text Logo (from login.php) */
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

        /* Status Bar (Logged in & Logout) */
        .status-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            padding: 8px 0;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
        .status-bar span {
            color: var(--text-muted);
        }
        .status-bar span strong {
            color: var(--gold-light); /* Gold */
        }
        .status-bar a {
            color: var(--orange); /* Orange */
            text-decoration: none;
            margin-left: 20px;
            font-weight: 600;
        }
        .status-bar a:hover {
            text-decoration: underline;
        }

        /* Prompt Line */
        .prompt-line {
            margin-bottom: 10px;
            font-size: 1em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .prompt-symbol {
            color: var(--gold); /* Gold */
            font-weight: bold;
        }
        
        /* Form Elements */
        select, textarea {
            background-color: var(--input-bg);
            color: var(--text-main);
            font-family: 'IBM Plex Mono', monospace;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 15px;
            width: 100%;
            box-sizing: border-box;
            margin-top: 5px;
        }
        
        textarea {
            font-size: 14px;
            line-height: 1.6;
            min-height: 200px;
            resize: vertical;
        }
        
        select {
            font-size: 1em;
            width: auto;
            min-width: 250px;
        }

        select:focus, textarea:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.25);
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
            margin-top: 20px;
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
        
        /* Output Log */
        .output-header {
            color: var(--text-muted);
            font-size: 0.9em;
            margin-top: 30px;
            text-align: center;
            border-bottom: 1px dashed var(--border-color);
            padding-bottom: 5px;
            text-transform: uppercase;
        }
        pre#output {
            background-color: #0d0d0d; /* Even darker bg for output */
            border: 1px solid var(--border-color);
            color: #e6edf3;
            padding: 18px;
            border-radius: 8px;
            overflow-x: auto;
            min-height: 50px;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 14px;
            line-height: 1.6;
            margin-top: 10px;
        }
        
        /* Status Box (Themed) */
        .status-box {
            font-weight: 500;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .status-success {
            background-color: #2a4832; 
            color: #a3d9b1; 
            border: 1px solid #a3d9b1;
        }
        .status-error {
            background-color: #721c24; 
            color: #f8d7da; 
            border: 1px solid #f5c6cb;
        }

        /* Footer */
        footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            color: #888;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>

    <div class="terminal-window">
        <!-- Logo Teks dari login.php -->
        <div class="logo">cpdeploy<span>.ai</span></div>
        
        <div class="status-bar">
            <!-- Teks Diterjemah -->
            <span>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
            <div>
                <a href="servers.php">[ Manage Servers ]</a>
                <a href="logout.php">[ Logout ]</a>
            </div>
        </div>

        <div class="prompt-line">
            <span class="prompt-symbol">cpdeploy:~$</span>
            <label for="server-select">deploy-to</label>
            <select id="server-select">
                <?php if (empty($servers)): ?>
                    <option value="">-- No servers found --</option>
                <?php else: ?>
                    <?php foreach ($servers as $server): ?>
                        <option value="<?php echo $server['id']; ?>">
                            <?php echo htmlspecialchars($server['display_name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <?php if (empty($servers)): ?>
                <!-- Teks Diterjemah -->
                <a href="servers.php" style="color: var(--orange); margin-left: 10px;">(Add a server)</a>
            <?php endif; ?>
        </div>

        <div class="prompt-line">
            <span class="prompt-symbol">cpdeploy:~$</span>
            <label for="prompt-input">exec-prompt</label>
        </div>
        <!-- Teks Diterjemah -->
        <textarea id="prompt-input" placeholder="// Type your command here - contoh: bina sebuah laman blog"></textarea>
        
        <button id="execute-btn" <?php if (empty($servers)) echo 'disabled'; ?>>
            <!-- Teks Diterjemah -->
            <?php echo empty($servers) ? 'Please add a server first' : 'Execute'; ?>
        </button>

        <h3 class="output-header">--- [ CONSOLE OUTPUT LOG ] ---</h3>
        <div id="status-message" class="status-box" style="display:none;"></div>
        <!-- Teks Diterjemah -->
        <pre id="output">Awaiting command...</pre>

        <footer>
            Copyright &copy; <?php echo date("Y"); ?> M A T N E T -  H O S T
        </footer>
    </div>

    <script>
        const promptInput = document.getElementById('prompt-input');
        const executeBtn = document.getElementById('execute-btn');
        const outputEl = document.getElementById('output');
        const statusMsg = document.getElementById('status-message');
        const serverSelect = document.getElementById('server-select');

        executeBtn.addEventListener('click', async () => {
            const prompt = promptInput.value;
            const serverId = serverSelect.value;

            if (!prompt) {
                alert('Please enter a prompt.');
                return;
            }
            if (!serverId) {
                alert('Please select a server or add a new one.');
                return;
            }

            executeBtn.disabled = true;
            statusMsg.style.display = 'none';
            let aiPlan = null; 

            try {
                // --- LANGKAH 1: PANGGIL AI GENERATOR ---
                executeBtn.textContent = '1/2: Contacting AI...';
                outputEl.textContent = 'Sending prompt to AI...';

                const aiResponse = await fetch('ai_generator.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ prompt: prompt })
                });

                const aiResult = await aiResponse.json();
                if (!aiResponse.ok || aiResult.status !== 'success') {
                    if (aiResponse.status === 403) throw new Error('Access Denied. Session expired.');
                    throw new Error(aiResult.message || 'AI Error.');
                }
                aiPlan = aiResult.ai_plan;
                outputEl.textContent = "AI Plan Received:\n" + JSON.stringify(aiPlan, null, 2);

                // --- LANGKAH 2: PANGGIL CPANEL EXECUTOR ---
                executeBtn.textContent = '2/2: Executing on cPanel...';
                outputEl.textContent += "\n\nSending plan to cPanel...";

                const cpanelResponse = await fetch('cpanel_executor.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        ai_plan: aiPlan,
                        server_id: serverId 
                    })
                });

                const cpanelResult = await cpanelResponse.json();
                if (!cpanelResponse.ok) {
                    if (cpanelResponse.status === 403) throw new Error('Access Denied. Session expired.');
                    throw new Error(cpanelResult.message || 'cPanel Error.');
                }

                // --- SELESAI ---
                outputEl.textContent = JSON.stringify(cpanelResult, null, 2);
                statusMsg.textContent = 'Status: Executed Successfully!';
                statusMsg.className = 'status-box status-success';
                statusMsg.style.display = 'block';

            } catch (error) {
                outputEl.textContent = 'Critical Error:\n' + error.message;
                statusMsg.textContent = 'Status: Error Occurred. Check output.';
                statusMsg.className = 'status-box status-error';
                statusMsg.style.display = 'block';
                if (error.message.includes('Access Denied')) {
                    setTimeout(() => { window.location.href = 'login.php'; }, 2000);
                }
            } finally {
                executeBtn.disabled = false;
                executeBtn.textContent = 'Execute';
            }
        });
    </script>
</body>
</html>