<?php
require_once '../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class JwtAuth {
    public static function generateToken($user) {
        $payload = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'exp' => time() + (60 * 60) // 1 hour expiration
        ];

        return JWT::encode($payload, JWT_SECRET, 'HS256');
    }

    public static function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new key (JWT_SECRET, 'HS256'));
            return (array) $decoded;
        } catch (Exception $e) {
            echo "Error decoding JWT: " . $e->getMessage();
            return false;
        }
    }

    public static function signAuthPayload($authorizationPayload) {
        return JWT::encode($authorizationPayload, JWT_SECRET, 'HS256');
    }

}