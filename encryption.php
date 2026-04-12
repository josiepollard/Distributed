<?php
function encryptMessage($plaintext) {
    $key = 'your-secret-key-CHANGE-THIS-123456'; // store securely in real app
    $iv = random_bytes(16);

    $cipher = openssl_encrypt(
        $plaintext,
        'AES-256-CBC',
        $key,
        0,
        $iv
    );

    return base64_encode($iv . $cipher);
}

function decryptMessage($encrypted) {
    $key = 'your-secret-key-CHANGE-THIS-123456';

    $data = base64_decode($encrypted, true);

    // If not valid base64 OR too short → return original (old message)
    if ($data === false || strlen($data) < 17) {
        return $encrypted;
    }

    $iv = substr($data, 0, 16);
    $cipher = substr($data, 16);

    $decrypted = openssl_decrypt(
        $cipher,
        'AES-256-CBC',
        $key,
        0,
        $iv
    );

    // fallback if decryption fails
    return $decrypted !== false ? $decrypted : $encrypted;
}
?>