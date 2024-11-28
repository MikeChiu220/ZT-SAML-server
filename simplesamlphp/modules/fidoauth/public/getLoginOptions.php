<?php
session_start();
// 引入 SimpleSAMLphp 的 Autoload
$path= dirname(__FILE__).'/../../../lib/_autoload.php';
require_once(dirname(__FILE__).'/../../../lib/_autoload.php');
require_once('common.php');

// 接收前端傳來的使用者資訊
$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'];
$challenge =  base64url_encode($data['challenge']??random_bytes(32));

// 生成一個隨機的 challenge
//$challenge = base64url_encode(random_bytes(32));

// 查找用戶的已註冊憑證
$query = $db->prepare('SELECT credential_id,public_key FROM credentials INNER JOIN users ON credentials.user_id = users.id WHERE users.username = ?');
$query->execute([$username]);
$credentials = [];
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $credential_id = $row['credential_id'];
    $decodeId = base64url_encode($credential_id);
    $credentials[] = [
        'type' => 'public-key',
        'id' => base64url_decode($row['credential_id']) // 使用 Base64URL 解碼
    ];
    $_SESSION['public_key'] = $row['public_key'];
}
// 建立 WebAuthn 登入選項
$options = [
    'challenge' => $challenge,
    'allowCredentials' => $credentials,
    'timeout' => 60000,
//  'userVerification' => 'preferred',      // request user key in pin code
    'userVerification' => "discouraged",    // Avoids asking for PIN or biometric if possible
];
// Store the challenge and rpId in the session or database
$_SESSION['challenge'] = $challenge;

/*
$row = $query->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => 'User or credential not found']);
    exit;
}

// 建立 WebAuthn 登入選項
$options = [
    'publicKey' => [
        'challenge' => base64_encode($challenge),
        'allowCredentials' => [
            [
                'type' => 'public-key',
                'id' => base64_encode($row['credential_id']) // 使用伺服器已保存的憑證ID
            ]
        ],
        'timeout' => 60000,
        'userVerification' => 'preferred',
    ]
];
*/

header('Content-Type: application/json');
echo json_encode($options);
