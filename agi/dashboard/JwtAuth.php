<?php
require_once 'vendor/autoload.php';
use \Firebase\JWT\JWT;

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
            $decoded = JWT::decode($token, JWT_SECRET, ['HS256']);
            return (array) $decoded;
        } catch (Exception $e) {
            return false;
        }
    }
}