<?php
class SFD_URL_Encryptor {
    private static $encryption_key;
    
    public static function init() {
        self::$encryption_key = wp_salt('auth');
    }
    
    public static function encrypt_url($url) {
        $encrypted = openssl_encrypt($url, 'AES-256-CBC', self::$encryption_key, 0, substr(self::$encryption_key, 0, 16));
        return urlencode(base64_encode($encrypted));
    }
    
    public static function decrypt_url($encrypted_url) {
        $decoded = base64_decode(urldecode($encrypted_url));
        return openssl_decrypt($decoded, 'AES-256-CBC', self::$encryption_key, 0, substr(self::$encryption_key, 0, 16));
    }
}