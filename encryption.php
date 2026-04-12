<?php

//encrypt msg
function encryptMessage($plaintext) {

    $key = '9f3kL8xP2qZ7vW1mA5dR6sT0yU4nB8cH'; //hardcoded key

    $iv = random_bytes(16);

    //encrypt the message
    $cipher = openssl_encrypt(
        $plaintext,
        'AES-256-CBC',
        $key,
        0,
        $iv
    );

    return base64_encode($iv . $cipher);
}


//decrypt
function decryptMessage($encrypted) {
    $key = '9f3kL8xP2qZ7vW1mA5dR6sT0yU4nB8cH'; //same key as above

    $data = base64_decode($encrypted, true);

    // If not valid base64 OR too short, return original 
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