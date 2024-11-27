<?php
require_once 'JwtAuth.php';

class JwtMiddleware {
    public static function authenticate() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            header("HTTP/1.0 401 Unauthorized");
            echo json_encode(['error' => 'No token provided']);
            exit;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $decoded = JwtAuth::validateToken($token);

        if (!$decoded) {
            header("HTTP/1.0 401 Unauthorized");
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }

        // Token is valid, you can now access $decoded['user_id'] and $decoded['username']
    }
}