<?php
// crypto.php — Modul kripto seragam untuk LAKSANA
// Kaedah: AES-256-GCM (selamat, moden, cepat)
// ===============================================

/**
 * Ambil kunci rahsia dari environment variable.
 * Jalankan di shell/server: export LAKSANA_SECRET_KEY='your-very-strong-secret'
 */
function laksana_get_key(): string {
    $raw = getenv('LAKSANA_SECRET_KEY');
    if (!$raw && defined('LAKSANA_SECRET_KEY')) {
        $raw = LAKSANA_SECRET_KEY;
    }
    if (!$raw) {
        throw new Exception('LAKSANA_SECRET_KEY belum ditetapkan di environment/server.');
    }
    return hash('sha256', $raw, true); // jadi 32 byte key
}

/**
 * Encrypt plaintext → base64(iv):base64(tag):base64(ciphertext)
 */
function encrypt_data(string $plaintext): string {
    $key = laksana_get_key();
    $iv  = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
    if ($cipher === false) throw new Exception('Encrypt gagal.');
    return base64_encode($iv) . ':' . base64_encode($tag) . ':' . base64_encode($cipher);
}

/**
 * Decrypt dari format base64(iv):base64(tag):base64(ciphertext)
 * Jika format tidak sah, pulangkan plaintext asal.
 */
function decrypt_data(string $blob) {
    $parts = explode(':', $blob);
    if (count($parts) !== 3) {
        return $blob; // fallback: mungkin plaintext
    }
    [$b64iv, $b64tag, $b64ct] = $parts;
    $iv  = base64_decode($b64iv, true);
    $tag = base64_decode($b64tag, true);
    $ct  = base64_decode($b64ct, true);
    if ($iv === false || $tag === false || $ct === false) {
        throw new Exception('Format token tidak sah.');
    }
    $key = laksana_get_key();
    $pt  = openssl_decrypt($ct, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '');
    if ($pt === false) throw new Exception('Decrypt gagal. Semak LAKSANA_SECRET_KEY.');
    return $pt;
}

