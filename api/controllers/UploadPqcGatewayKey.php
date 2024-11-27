<?php
class UploadPqcGatewayKey {
    private $db;

    public function __construct() {
        // Create a database connection
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // Check for connection errors
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    public function uploadPublicKey() {
        // Get parameter
        $boxId = $_GET['boxId'] ?? '';
        $publicKey = _GET['publicKey'] ?? '';

        // Fetch deviceId from database based on parameters
        // Query to get gateway status
        $query = "SELECT deviceId FROM gateway_Info WHERE deviceId = $boxId";
        $queryResult = $this->db->query($query);
        // Check for query errors
        if (!$queryResult) {
            die("Query failed: " . $this->db->error);
        }
        if ($queryResult->num_rows)
            $sqlCommand = "UPDATE gateway_Info SET publicKey='$publicKey' WHERE deviceId=$boxId";
        else
            $sqlCommand = "INSERT INTO gateway_Info SET deviceId='$boxId', publicKey='$publicKey'";
        
        $queryResult = $this->db->query($query);
        
        $result = [
            'success' => true
        ];

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function getSignAuth() {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate username and password
        $boxId = $data['boxId'] ??'';
        $username = $data['username'] ??'';
        $policy = $data['policy'] ??'';
        $boxToken = $data['boxToken'] ??'';
        if ($boxId && $username && $policy && $boxToken) {
            $authorizationPayload = [
                'boxId' => $boxId,
                'username' => $username,
                'policy' => $policy,
                'boxToken' => $boxToken
            ];
            $authorizationHeader = JwtAuth::signAuthPayload($authorizationPayload);
            $result = [
                'result' => true,
                'authorizationHeader' => $authorizationHeader
            ];
            
            header('Content-Type: application/json');
            echo json_encode($result);
        } else {
            header("HTTP/1.0 401 Unauthorized");
            echo json_encode(['error' => 'Invalid credentials']);
        }
    }
}