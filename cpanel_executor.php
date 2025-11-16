<?php
/**
 * cpanel_executor.php — Versi 2.2.1
 * Penambahbaikan:
 * - Menggunakan 'crypto.php' untuk semua fungsi kripto.
 * - Bakinya kekal (aksi fail/dir, MySQL, subdomain, email POP, git_deploy)
 */

session_start();
require_once 'db.php';

// 1. MUATKAN KUNCI RAHSIA DAHULU
require_once 'local_env.php'; 

// 2. SEKARANG BARU MUATKAN FUNGSI CRYPTO
// Baris ini memuatkan:
// - laksana_get_key()
// - encrypt_data()
// - decrypt_data()
require_once 'crypto.php';

// ===== BLOK KOD KRIPTO YANG PENDUA TELAH DIPADAM DARI SINI =====
// (Fungsi-fungsi itu kini dimuatkan oleh crypto.php di atas)
// ==============================================================


// 1) Akses (Bermula serta-merta selepas 'require')
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses Ditolak.']);
    exit;
}

// 2) Input
$input       = json_decode(file_get_contents('php://input'), true);
$action_list = $input['ai_plan'] ?? null;
$server_id   = $input['server_id'] ?? null;
$user_id     = $_SESSION['user_id'];

if (empty($action_list) || !is_array($action_list) || empty($server_id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Rancangan AI (array) atau ID Pelayan tidak diterima.']);
    exit;
}

// 3) Dapatkan kredensial cPanel
try {
    $stmt = $conn->prepare("SELECT host, cpanel_user, api_token FROM cpanel_accounts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $server_id, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) throw new Exception("Pelayan tidak ditemui atau anda tiada akses.");
    $stmt->bind_result($host, $cpanel_user, $api_token);
    $stmt->fetch();
    $stmt->close();

    // Gunakan fungsi decrypt_data() yang dimuatkan dari crypto.php
    $decrypted_token = decrypt_data($api_token);
    // (Pemeriksaan 'false' tidak diperlukan kerana crypto.php anda akan 'throw Exception')

    $CPANEL_HOST  = $host;
    $CPANEL_USER  = $cpanel_user;
    $CPANEL_TOKEN = $decrypted_token;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ralat Kredential: '.$e->getMessage()]);
    exit;
}

// 4) Laksana
$execution_log = [];
foreach ($action_list as $action) {
    try {
        $type = $action['action_type'] ?? '';

        switch ($type) {

            // ====== Fail & Direktori ======
  /*          case 'create_folder': {
                $absPath = "/home/{$CPANEL_USER}" . $action['folder_path'];
                call_cpanel_api2('Fileman','mkdir',[
                    'path' => dirname($absPath),
                    'name' => basename($absPath),
                    'permissions' => '0755'
                ], $CPANEL_HOST, $CPANEL_USER, $CPANEL_TOKEN);
                $execution_log[] = ['action'=>'create_folder','path'=>$action['folder_path'],'status'=>'success'];
                break;
            }
*/
// ====== Fail & Direktori ======
            case 'create_folder': {
                // Gunakan 'ensure_dir_exists_api2' untuk mencipta
                // keseluruhan laluan (path) dengan selamat.
                $absPath = "/home/{$CPANEL_USER}" . $action['folder_path'];
                ensure_dir_exists_api2($absPath, $CPANEL_HOST, $CPANEL_USER, $CPANEL_TOKEN);
                
                $execution_log[] = ['action'=>'create_folder','path'=>$action['folder_path'],'status'=>'success'];
                break;
            }



            case 'create_file': {
                $absDir = "/home/{$CPANEL_USER}" . dirname($action['file_path']);
                ensure_dir_exists_api2($absDir, $CPANEL_HOST, $CPANEL_USER, $CPANEL_TOKEN);
                call_cpanel_uapi('Fileman','save_file_content',[
                    'dir'     => $absDir,
                    'file'    => basename($action['file_path']),
                    'content' => $action['file_content']
                ], $CPANEL_HOST, $CPANEL_USER, $CPANEL_TOKEN);
                $execution_log[] = ['action'=>'create_file','path'=>$action['file_path'],'status'=>'success'];
                break;
            }

            case 'upload_file': {
                $absDir = "/home/{$CPANEL_USER}" . $action['destination_dir'];
                ensure_dir_exists_api2($absDir, $CPANEL_HOST, $CPANEL_USER, $CPANEL_TOKEN);

                $tmp = tempnam(sys_get_temp_dir(), 'laksana_');
                file_put_contents($tmp, base64_decode($action['file_b64']));

                call_cpanel_uapi_multipart('Fileman','upload_files',[
                    'dir' => $absDir
                ], [
                    'file-1' => [$tmp, $action['file_name'], mime_content_type($tmp) ?: 'application/octet-stream']
                ], $CPANEL_HOST, $CPANEL_USER, $CPANEL_TOKEN);

                @unlink($tmp);
                $execution_log[] = ['action'=>'upload_file','dest'=>$action['destination_dir'].'/'.$action['file_name'],'status'=>'success'];
                break;
            }

            case 'get_file_content': {
                $absPath = "/home/{$CPANEL_USER}" . $action['path'];
                $res = call_cpanel_uapi('Fileman','get_file_content',[
                    'path' => $absPath
                ], $CPANEL_HOST, $CPANEL_USER, $CPANEL_TOKEN);
                $content = $res['data']['content'] ?? '';
                $execution_log[] = ['action'=>'get_file_content','path'=>$action['path'],'status'=>'success','content'=>$content];
                break;
            }

            case 'list_dir': {
                $absDir = "/home/{$CPANEL_USER}" . $action['dir_path'];
                $res = call_cpanel_uapi('Fileman','list_files',[
                    'dir' => $absDir
                ], $CPANEL_HOST, $CPANEL_USER, $CPANEL_TOKEN);
                $files = $res['data'] ?? [];
                $execution_log[] = ['action'=>'list_dir','dir'=>$action['dir_path'],'status'=>'success','items'=>$files];
                break;
            }

            // ====== MySQL ======
            case 'create_db': {
                $db_full   = $CPANEL_USER.'_'.$action['db_name'];
                $user_full = $CPANEL_USER.'_'.$action['db_user'];
                $pass      = $action['db_password'];

                call_cpanel_uapi('Mysql','create_database', ['name'=>$db_full], $CPANEL_HOST,$CPANEL_USER,$CPANEL_TOKEN);
                call_cpanel_uapi('Mysql','create_user',     ['name'=>$user_full,'password'=>$pass], $CPANEL_HOST,$CPANEL_USER,$CPANEL_TOKEN);
                call_cpanel_uapi('Mysql','set_privileges_on_database', [
                    'user'=>$user_full,'database'=>$db_full,'privileges'=>'ALL PRIVILEGES'
                ], $CPANEL_HOST,$CPANEL_USER,$CPANEL_TOKEN);

                if (!empty($action['sql_query'])) {
                    $installer = 'laksana_sql_'.uniqid().'.php';
                    $absDirPH  = "/home/{$CPANEL_USER}/public_html";
                    $code = "<?php
                        \$c = new mysqli('localhost','{$user_full}','{$pass}','{$db_full}');
                        if (\$c->connect_error) die('ConnErr: '.\$c->connect_error);
                        if (\$c->multi_query(\"".addslashes($action['sql_query'])."\")) { echo 'SQL OK'; } else { echo 'SQL ERR: '.\$c->error; }
                        \$c->close();
                        unlink(__FILE__);
                    ?>";
                    call_cpanel_uapi('Fileman','save_file_content',[
                        'dir' => $absDirPH,
                        'file'=> $installer,
                        'content'=>$code
                    ], $CPANEL_HOST,$CPANEL_USER,$CPANEL_TOKEN);

                    $url = "https://{$CPANEL_HOST}/{$installer}";
                    $ch = curl_init($url);
                    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true, CURLOPT_SSL_VERIFYPEER=>false, CURLOPT_SSL_VERIFYHOST=>false]);
                    $exec = curl_exec($ch); curl_close($ch);
                }

                $execution_log[] = ['action'=>'create_db','db_name'=>$db_full,'status'=>'success'];
                break;
            }

            // ====== Subdomain ======
            case 'create_subdomain': {
                $primary = get_primary_domain($CPANEL_HOST,$CPANEL_USER,$CPANEL_TOKEN);
                if (!$primary) throw new Exception('Gagal dapat primary domain.');
                $absRoot = "/home/{$CPANEL_USER}" . $action['document_root'];
                ensure_dir_exists_api2($absRoot, $CPANEL_HOST,$CPANEL_USER,$CPANEL_TOKEN);

                $label = $action['domain_name'];
                if (strpos($label,'.') !== false) { $label = explode('.',$label)[0]; }

                call_cpanel_uapi('SubDomain','addsubdomain',[
                    'domain'     => $label,
                    'rootdomain' => $primary,
                    'dir'        => $absRoot
                ], $CPANEL_HOST,$CPANEL_USER,$CPANEL_TOKEN);

                $execution_log[] = ['action'=>'create_subdomain','domain'=>$label.'.'.$primary,'status'=>'success'];
                break;
            }

            // ====== E-mel POP ======
            case 'create_email': {
                $addr = $action['email'];
                if (strpos($addr,'@') === false) throw new Exception('Format e-mel tidak sah.');
                list($local,$domain) = explode('@',$addr,2);

                $quota = isset($action['quota']) ? (int)$action['quota'] : 250; // MB
                call_cpanel_uapi('Email','add_pop',[
                    'email'    => $local,
                    'domain'   => $domain,
                    'password' => $action['password'],
                    'quota'    => $quota
                ], $CPANEL_HOST,$CPANEL_USER,$CPANEL_TOKEN);

                $execution_log[] = ['action'=>'create_email','email'=>$addr,'status'=>'success'];
                break;
            }

            // ====== Git Deployment ======
            case 'git_deploy': {
                $repoRoot = $action['repository_root'];
                call_cpanel_uapi('VersionControlDeployment','create',[
                    'repository_root' => $repoRoot
                ], $CPANEL_HOST,$CPANEL_USER,$CPANEL_TOKEN);

                $execution_log[] = ['action'=>'git_deploy','repository_root'=>$repoRoot,'status'=>'success'];
                break;
            }

            // ====== Log Meta ======
            case 'deploy_meta':
            case 'deploy_to_cpanel': {
                $execution_log[] = ['action'=>$type,'status'=>'info','log'=>$action['deployment_log'] ?? null];
                break;
            }

            default:
                throw new Exception("Jenis tindakan tidak difahami: ".$type);
        }

    } catch (Exception $e) {
        $execution_log[] = [
            'action' => $action['action_type'] ?? 'unknown',
            'path'   => $action['file_path'] ?? $action['folder_path'] ?? $action['db_name'] ?? $action['email'] ?? 'N/A',
            'status' => 'failed',
            'error'  => $e->getMessage()
        ];
    }
}

// 5) Output
header('Content-Type: application/json');
echo json_encode(['status'=>'berjaya','execution_log'=>$execution_log]);

// ============================
// HELPERS
// ============================
function call_cpanel_uapi($module, $function, $params, $host, $user, $token) {
    $q = http_build_query($params,'','&');
    $url = "https://{$host}:2083/execute/{$module}/{$function}?{$q}";
    $ch  = curl_init($url);
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_SSL_VERIFYHOST=>false,
        CURLOPT_HTTPHEADER=>["Authorization: cpanel {$user}:{$token}"]
    ]);
    $res = curl_exec($ch);
    $http= curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) throw new Exception("cURL UAPI: ".$err);
    $json = json_decode($res,true);
    if ($http!==200 || (isset($json['status']) && $json['status']==0)) {
        $msg = $json['errors'][0] ?? $res;
        throw new Exception("UAPI error: ".$msg);
    }
    return $json;
}

function call_cpanel_api2($module, $function, $params, $host, $user, $token) {
    $base = [
        'cpanel_jsonapi_user'       => $user,
        'cpanel_jsonapi_apiversion' => 2,
        'cpanel_jsonapi_module'     => $module,
        'cpanel_jsonapi_func'       => $function,
    ];
    $q = http_build_query(array_merge($base,$params),'','&');
    $url = "https://{$host}:2083/json-api/cpanel?{$q}";
    $ch  = curl_init($url);
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_SSL_VERIFYHOST=>false,
        CURLOPT_HTTPHEADER=>["Authorization: cpanel {$user}:{$token}"]
    ]);
    $res = curl_exec($ch);
    $http= curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) throw new Exception("cURL API2: ".$err);
    $json = json_decode($res,true);
    if ($http!==200 || (isset($json['cpanelresult']['error']) && $json['cpanelresult']['error'])) {
        $msg = $json['cpanelresult']['error'] ?? $res;
        throw new Exception("API2 error: ".$msg);
    }
    return $json;
}

// Multipart untuk Fileman::upload_files
function call_cpanel_uapi_multipart($module, $function, $fields, $files, $host, $user, $token) {
    $url = "https://{$host}:2083/execute/{$module}/{$function}";
    $postfields = [];
    foreach ($fields as $k=>$v) $postfields[$k] = $v;
    foreach ($files as $fieldName => $fileSpec) {
        list($path, $name, $mime) = $fileSpec;
        $postfields[$fieldName] = new CURLFile($path, $mime, $name);
    }
    $ch = curl_init($url);
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_SSL_VERIFYHOST=>false,
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>$postfields,
        CURLOPT_HTTPHEADER=>["Authorization: cpanel {$user}:{$token}"]
    ]);
    $res = curl_exec($ch);
    $http= curl_getinfo($ch,CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) throw new Exception("cURL UAPI multipart: ".$err);
    $json = json_decode($res,true);
    if ($http!==200 || (isset($json['status']) && $json['status']==0)) {
        $msg = $json['errors'][0] ?? $res;
        throw new Exception("UAPI multipart error: ".$msg);
    }
    return $json;
}

function ensure_dir_exists_api2($absDir, $host, $user, $token) {
    $parts = array_filter(explode('/', trim($absDir,'/')));
    $curr = '';
    foreach ($parts as $p) {
        $next = $curr.'/'.$p;
        try {
            call_cpanel_api2('Fileman','mkdir',[
                'path' => $curr===''? '/':$curr,
                'name' => $p,
                'permissions' => '0755'
            ], $host, $user, $token);
        } catch (Exception $e) {
            if (stripos($e->getMessage(),'File exists')===false) throw $e;
        }
        $curr = $next;
    }
}

function get_primary_domain($host, $user, $token) {
    // 1. Cuba panggil UAPI DomainInfo::list_domains
    // Dokumentasi: https://api.docs.cpanel.net/openapi/cpanel/operation/list_domains/
    $res = call_cpanel_uapi('DomainInfo', 'list_domains', [], $host, $user, $token);

    // CARA 1: Semak jika cPanel terus beri 'main_domain' dalam data
    if (isset($res['data']['main_domain']) && !empty($res['data']['main_domain'])) {
        return $res['data']['main_domain'];
    }

    // CARA 2: Semak dalam senarai array 'domains'
    if (isset($res['data']['domains']) && is_array($res['data']['domains'])) {
        foreach ($res['data']['domains'] as $domain) {
            // Cari yang jenisnya 'main'
            if (isset($domain['type']) && $domain['type'] === 'main') {
                return $domain['domain'];
            }
        }
        // Fallback: Ambil domain pertama jika wujud
        if (count($res['data']['domains']) > 0) {
            return $res['data']['domains'][0]['domain'];
        }
    }

    // CARA 3 (Terdesak): Cuba panggil API2 DomainLookup::getbasedomains
    // Ini kadang-kadang lebih berkesan untuk akaun lama
    try {
        $res2 = call_cpanel_api2('DomainLookup', 'getbasedomains', [], $host, $user, $token);
        if (isset($res2['cpanelresult']['data']) && is_array($res2['cpanelresult']['data'])) {
            foreach ($res2['cpanelresult']['data'] as $d) {
                return $d['domain']; // Ambil yang pertama jumpa
            }
        }
    } catch (Exception $e) {
        // Abaikan ralat API2, kita fokus pada ralat utama bawah
    }

    // JIKA SEMUA GAGAL: Throw error dengan maklumat DEBUG
    // Ini akan memaparkan apa sebenarnya cPanel balas dalam log output anda
    $debug_info = json_encode($res); 
    throw new Exception("Gagal dapat primary domain. Respon cPanel: " . substr($debug_info, 0, 200) . "..."); 
}
?>
