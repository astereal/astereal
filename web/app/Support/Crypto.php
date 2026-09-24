<?php

declare(strict_types=1);

namespace Astereal\Web\Support;

use RuntimeException;

class Crypto
{
    protected const CIPHER = 'aes-256-cbc';

    /**
     * Retrieve the 32-byte encryption key from environment or security config
     */
    public static function getKey(): string
    {
        $rawKey = getenv('APP_KEY');

        if (!$rawKey) {
            $securityConfig = dirname(__DIR__, 2) . '/config/security.php';
            if (file_exists($securityConfig)) {
                $config = require $securityConfig;
                $rawKey = $config['app_key'] ?? ($config['api_secret'] ?? null);
            }
        }

        if (!$rawKey) {
            $rawKey = 'astereal_default_aes_key_32_bytes_long_12345';
        }

        if (str_starts_with($rawKey, 'base64:')) {
            $decoded = base64_decode(substr($rawKey, 7), true);
            if ($decoded !== false && strlen($decoded) === 32) {
                return $decoded;
            }
        }

        return hash('sha256', $rawKey, true);
    }

    /**
     * Encrypt a string using AES-256-CBC with HMAC-SHA256 authentication
     */
    public static function encrypt(string $value): string
    {
        $key = self::getKey();
        $ivLen = openssl_cipher_iv_length(self::CIPHER);
        $iv = openssl_random_pseudo_bytes($ivLen);

        $ciphertext = openssl_encrypt($value, self::CIPHER, $key, 0, $iv);
        if ($ciphertext === false) {
            throw new RuntimeException('Encryption failed.');
        }

        $mac = hash_hmac('sha256', $iv . $ciphertext, $key);

        $payload = json_encode([
            'iv'    => base64_encode($iv),
            'value' => $ciphertext,
            'mac'   => $mac,
        ]);

        return base64_encode($payload);
    }

    /**
     * Decrypt a string using AES-256-CBC and verify HMAC-SHA256 signature
     */
    public static function decrypt(string $payload): ?string
    {
        $key = self::getKey();
        $json = base64_decode($payload, true);
        if ($json === false) {
            return null;
        }

        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data['iv'], $data['value'], $data['mac'])) {
            return null;
        }

        $iv = base64_decode($data['iv'], true);
        $ciphertext = $data['value'];
        $mac = $data['mac'];

        if ($iv === false) {
            return null;
        }

        $expectedMac = hash_hmac('sha256', $iv . $ciphertext, $key);
        if (!hash_equals($expectedMac, $mac)) {
            return null; // Tampered or wrong key
        }

        $decrypted = openssl_decrypt($ciphertext, self::CIPHER, $key, 0, $iv);
        return $decrypted !== false ? $decrypted : null;
    }
}
