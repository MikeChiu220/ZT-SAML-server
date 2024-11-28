<?php
require_once '../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
$JWT_SECRET = file_get_contents('/var/www/ZT-SAML-server/simplesamlphp/cert/server.key');     // privateKey
$ES256_JWT_SECRET = file_get_contents('/var/www/ZT-SAML-server/simplesamlphp/cert/es256_privatekey.pem');     // ES256 privateKey

class JwtAuth {
    public static function generateToken($user) {
        global $JWT_SECRET;
        $payload = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'exp' => time() + (60 * 60) // 1 hour expiration
        ];

        return JWT::encode($payload, $JWT_SECRET, 'HS256');
    }

    public static function validateToken($token) {
        global $JWT_SECRET;
        try {
            $decoded = JWT::decode($token, new key ($JWT_SECRET, 'HS256'));
            return (array) $decoded;
        } catch (Exception $e) {
            echo "Error decoding JWT: " . $e->getMessage();
            return false;
        }
    }

    public static function signAuthPayload($authorizationPayload) {
        global $ES256_JWT_SECRET;
        $authorizationSign = JWT::encode($authorizationPayload, $ES256_JWT_SECRET, 'ES256');
        return $authorizationSign;
    }

}