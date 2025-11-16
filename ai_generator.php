<?php
// ai_generator.php — Versi Diperkasa (Architect Prompt + Full Action Set)
session_start();

// 1) Akses
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses Ditolak. Sila login.']);
    exit;
}

// 2) Had Masa
set_time_limit(300);

// --- 3) KUNCI API (Selamat) ---
require_once 'local_env.php'; 
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY')); 

if (!OPENAI_API_KEY) {
     http_response_code(500);
     echo json_encode(['status' => 'error', 'message' => 'OPENAI_API_KEY tidak ditetapkan di persekitaran (environment).']);
     exit;
}
// ----------------------------------------------


// 4) Input
$input = json_decode(file_get_contents('php://input'), true);
$user_prompt = $input['prompt'] ?? '';

if (!$user_prompt) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Prompt tidak diterima.']);
    exit;
}

// 5) Panggilan LLM
try {
    $ai_plan = call_llm_api($user_prompt); // Ini kini memulangkan ARRAY 'actions'

    // Pastikan berbentuk array aksi
    if (empty($ai_plan) || !is_array($ai_plan)) {
        if (isset($ai_plan['action_type'])) $ai_plan = [$ai_plan];
        else throw new Exception('AI tidak memulangkan JSON array "actions" yang sah.');
    }

    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'ai_plan' => $ai_plan]);

} catch (Exception $e) {
    http_response_code(500);
    // --- PEMBETULAN DI SINI (baris 53) ---
    echo json_encode(['status' => 'error', 'message' => 'Ralat API AI: '.$e->getMessage()]);
    exit;
}

// ============================
// FUNGSI
// ============================
function call_llm_api($prompt) {
    
    // --- DIKEMAS KINI: Arahan DB & Kata Laluan yang Jelas ---
    $system_prompt = '
Anda ialah "AI System Architect & Deployer" untuk platform cpdeploy.ai.
Tugas: faham idea pengguna → rancang seni bina → hasilkan kod & struktur fail → siapkan DB → subdomain → e-mel → deploy di cPanel.
Keluaran MESTI HANYA JSON OBJECT dengan satu kunci "actions" yang mengandungi ARRAY tindakan (tanpa teks lain).

KEHENDAK:
- Semua fail/folder berada di bawah "/public_html".
- Jika perlu DB, bina DB + user + privileges + seed SQL.
- Kata laluan untuk DB atau E-mel MESTI SANGAT KUAT (skor cPanel 65+): 16+ aksara, huruf besar/kecil, nombor, dan simbol (!@#$%).
- Apabila membangunkan aplikasi yang mempunyai DB, sila juga buat kandungan demo untuk DB tersebut contohnya user default dan juga kandungan demo.

SET AKSI YANG SAH (JSON OBJECTS):
1) Cipta folder:
{ "action_type": "create_folder", "folder_path": "/public_html/app_name/includes" }

2) Cipta fail (teks):
{ "action_type": "create_file", "file_path": "/public_html/app_name/index.php", "file_content": "<?php echo \"OK\"; ?>" }

3) Muat naik fail binari (base64):
{ "action_type": "upload_file", "destination_dir": "/public_html/app_name/assets", "file_name": "logo.png", "file_b64": "<BASE64_DATA>" }

4) Baca kandungan fail (untuk verifikasi/log):
{ "action_type": "get_file_content", "path": "/public_html/app_name/index.php" }

5) Senarai direktori:
{ "action_type": "list_dir", "dir_path": "/public_html/app_name" }

6) Cipta MySQL DB + user + privileges + seed SQL:
{
  "action_type": "create_db",
  "db_name": "appdb",       // Masukkan prefix usercpanel sebelum namadb contoh usercpanel_namadb
  "db_user": "appuser",     // Masukkan prefix usercpanel sebelum namadb contoh usercpanel_namadb
  "db_password": "L@ks@n@-Str0ngP@ss!2025", 
  "sql_query": "CREATE TABLE items(id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100));"
}

7) Cipta subdomain:
{
  "action_type": "create_subdomain",
  "domain_name": "app.domainanda.com",
  "document_root": "/public_html/app"
}

8) Cipta akaun e-mel POP:
{
  "action_type": "create_email",
  "email": "noreply@domainanda.com",
  "password": "S3cureEm@il!P@ss",
  "quota": 250
}

9) Git deployment (repositori cPanel sedia):
{
  "action_type": "git_deploy",
  "repository_root": "/home/CPANELUSER/repositories/app_repo", 
  "notes": "Deploy current branch"
}

10) Log meta:
{ "action_type": "deploy_meta", "deployment_log": "Ringkasan langkah" }

NOTA:
- Gunakan nama fail konsisten (config.php, dbconnect.php, /includes, /assets, /admin).
- Pastikan index.php menjalankan app secara asas.
- Letakkan contoh .htaccess jika perlu (contoh redirect HTTPS).
- Jika bina borang, sediakan handler PHP dan sanitasi ringkas.

CONTOH OUTPUT (WAJIB IKUT):
{
  "actions": [
    { "action_type": "create_folder", "folder_path": "/public_html/blog-x/includes" },
    { "action_type": "create_db", "db_name": "cpanelprefix_blogx", "db_user": "cpanelprefix_blogxusr", "db_password": "P@ssW0rdKuat!#123", "sql_query": "CREATE TABLE posts(...);" },
    { "action_type": "create_file", "file_path": "/public_html/blog-x/index.php", "file_content": "<?php /* blog */ ?>" },
    { "action_type": "deploy_meta", "deployment_log": "Blog siap." }
  ]
}
';
    // --- TAMAT PERUBAHAN PROMPT ---

    $payload = [
        'model' => 'gpt-5',
        'messages' => [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $prompt]
        ],
        // Paksa AI pulangkan JSON yang sah
        'response_format' => ['type' => 'json_object']
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer '.OPENAI_API_KEY
        ],
        CURLOPT_TIMEOUT => 290
    ]);
    $raw = curl_exec($ch);
    if ($err = curl_error($ch)) throw new Exception('cURL OpenAI: '.$err);
    curl_close($ch);

    $resp = json_decode($raw, true);
    if (!isset($resp['choices'][0]['message']['content'])) {
        throw new Exception('Respons AI tidak dijangka: '.$raw);
    }

    $text = $resp['choices'][0]['message']['content'];

    // Parser yang lebih selamat
    $decoded_json = json_decode($text, true);

    // Semak jika format { "actions": [...] } wujud
    if (!isset($decoded_json['actions']) || !is_array($decoded_json['actions'])) {
        // Fallback jika AI masih degil dan hantar array terus [ ... ]
        if (is_array($decoded_json) && isset($decoded_json[0]['action_type'])) {
            return $decoded_json;
        }
        // Fallback jika AI hantar satu aksi { "action_type": ... }
        if (isset($decoded_json['action_type'])) {
            return [$decoded_json]; // Balut sebagai array
        }
        
        throw new Exception('AI tidak memulangkan "actions" array yang sah dalam JSON.');
    }

    // Berjaya! Pulangkan array 'actions'
    return $decoded_json['actions'];
}
?>
