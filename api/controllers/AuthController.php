<?php
class AuthController {
    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate username and password
        if ($data['username'] == 'kinghold1' && $data['password'] == 'kinghold1')
            $authenticated = true;
        else
            $authenticated = false;

        if ($authenticated) {
            $user = [
                'id' => 1,
                'username' => $data['username']
            ];
            $token = JwtAuth::generateToken($user);
            
            $result = [
                'user' => $user,
                'token' => $token
            ];
            
            header('Content-Type: application/json');
            echo json_encode($result);
        } else {
            header("HTTP/1.0 401 Unauthorized");
            echo json_encode(['error' => 'Invalid credentials']);
        }
    }
}