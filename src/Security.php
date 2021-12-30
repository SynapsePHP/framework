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
}

/*
 *
 * encrypt
 * decrypt
 * hash
 * generateCSRToken
 * verifyCSRToken
 * generateJWT
 * verifyJWT
 * decodeJWT
 *
 */