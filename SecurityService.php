<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use ParagonIE\ConstantTime\Base64;
use RuntimeException;

class SecurityService
{
    public function generateChaCha20Key()
    {
        return random_bytes(32); // 256-bit key for ChaCha20
    }

    public function encryptWithChaCha20($data, $key = null)
    {
        $key = $key ?: $this->generateChaCha20Key();
        $nonce = random_bytes(12); // 96-bit nonce for ChaCha20-Poly1305
        
        $encrypted = openssl_encrypt(
            $data,
            'chacha20-poly1305',
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );

        if ($encrypted === false) {
            throw new RuntimeException('Encryption failed');
        }

        return [
            'ciphertext' => Base64::encode($nonce . $tag . $encrypted),
            'key' => Base64::encode($key)
        ];
    }

    public function decryptWithChaCha20($encryptedData, $key)
    {
        $data = Base64::decode($encryptedData);
        $nonce = substr($data, 0, 12);
        $tag = substr($data, 12, 16);
        $ciphertext = substr($data, 28);

        $decrypted = openssl_decrypt(
            $ciphertext,
            'chacha20-poly1305',
            Base64::decode($key),
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );

        if ($decrypted === false) {
            throw new RuntimeException('Decryption failed');
        }

        return $decrypted;
    }

    public function generateSecureAuthSystem()
    {
        return [
            'jwt_secret' => Str::random(64),
            'encryption_key' => $this->generateChaCha20Key(),
            'api_keys' => [
                'database' => Str::random(32),
                'storage' => Str::random(32),
                'external' => Str::random(32)
            ]
        ];
    }

    public function addSecurityHeaders($code)
    {
        $securityHeaders = [
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'"
        ];

        // Add security headers to server code
        if (isset($code['server.js'])) {
            $code['server.js'] = str_replace(
                'app.listen(PORT, () => {',
                "app.use((req, res, next) => {\n" . 
                $this->generateSecurityHeadersCode($securityHeaders) .
                "    next();\n});\n\napp.listen(PORT, () => {",
                $code['server.js']
            );
        }

        return $code;
    }

    protected function generateSecurityHeadersCode($headers)
    {
        $code = '';
        foreach ($headers as $name => $value) {
            $code .= "    res.setHeader('{$name}', '{$value}');\n";
        }
        return $code;
    }
}