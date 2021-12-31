<?php

namespace Synapse;

use voku\helper\AntiXSS;

class Security
{
    /**
     *
     * Cleanse a string from possible XSS attacks
     *
     * @param string $string
     * @return string
     *
     */
    public function cleanse(string $string): string
    {
        $antixss = new AntiXSS();
        return $antixss->xss_clean($string);
    }

    /**
     *
     * Encrypt string using very strong algorithm (AES-256-GCM)
     *
     * @param string $value
     * @return string
     *
     */
    public static function encrypt(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $key       = substr(hash('sha256', $_ENV['HASH_SECURITY'], true), 0, 32);
        $cipher    = 'aes-256-gcm';
        $ivLen     = openssl_cipher_iv_length($cipher);
        $tagLength = 16;
        $iv        = openssl_random_pseudo_bytes($ivLen, $strong);
        $tag       = '';

        $encrypted = openssl_encrypt($value, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag, '', $tagLength);
        return base64_encode($iv.$encrypted.$tag);
    }

    /**
     *
     * Decrypt a Synapse encrypted string
     *
     * @param string $value
     * @return string
     *
     */
    public static function decrypt(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $encrypted = base64_decode($value);
        $key       = substr(hash('sha256', $_ENV['HASH_SECURITY'], true), 0, 32);
        $cipher    = 'aes-256-gcm';
        $ivLen     = openssl_cipher_iv_length($cipher);
        $tagLength = 16;
        $iv        = substr($encrypted, 0, $ivLen);
        $text      = substr($encrypted, $ivLen, -$tagLength);
        $tag       = substr($encrypted, -$tagLength);

        return openssl_decrypt($text, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
    }

    /**
     *
     * Hash a string with a good algorithm
     *
     * @param string $string
     * @return string
     *
     */
    public function hash(string $string): string
    {
        return hash('sha256', $string);
    }

    /**
     *
     * Generate a CSRF token for the current user
     *
     * @return string
     *
     */
    public function generateCSRFToken(): string
    {
        $token  = bin2hex(openssl_random_pseudo_bytes(16));
        $expire = time() + 300;

        $_SESSION['synapse_csrf_token']  = $token;
        $_SESSION['synapse_csrf_expire'] = $expire;

        return $token;
    }

    public function verifyCSRFToken(string $token): bool
    {
        $tok = $_SESSION['synapse_csrf_token'];
        $exp = $_SESSION['synapse_csrf_expire'];

        if (empty($tok)) { return false; }
        if ($tok !== $token) { return false; }
        if (time() > $exp) { return false; }

        return true;
    }

    // TODO: JWT Implementation
}