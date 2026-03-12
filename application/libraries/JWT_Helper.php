<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class JWT_Helper {

    private $secret;
    private $expiry = 86400;

    public function __construct() {
        $this->secret = getenv('JWT_SECRET') ?: 'tamdes-jwt-secret-change-in-production';
    }

    public function encode($payload) {
        $header = $this->base64url_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload['iat'] = time();
        $payload['exp'] = time() + $this->expiry;
        $payload_encoded = $this->base64url_encode(json_encode($payload));
        $signature = $this->base64url_encode(
            hash_hmac('sha256', "$header.$payload_encoded", $this->secret, true)
        );
        return "$header.$payload_encoded.$signature";
    }

    public function decode($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        list($header, $payload, $signature) = $parts;
        $valid_signature = $this->base64url_encode(
            hash_hmac('sha256', "$header.$payload", $this->secret, true)
        );
        if (!hash_equals($valid_signature, $signature)) return null;
        $data = json_decode($this->base64url_decode($payload));
        if (!$data || (isset($data->exp) && $data->exp < time())) return null;
        return $data;
    }

    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
