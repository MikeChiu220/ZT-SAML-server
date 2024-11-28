<?php
// 引入 SimpleSAMLphp 的 Autoload
$path= dirname(__FILE__).'/../../../lib/_autoload.php';
require_once(dirname(__FILE__).'/../../../lib/_autoload.php');
require_once('common.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['userId'] ?? '';

if (empty($userId)) {
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

// 查找用戶的已註冊憑證
$query = $db->prepare('SELECT display_name FROM users WHERE username = ?');
$query->execute([$userId]);
$credential = $query->fetch(PDO::FETCH_ASSOC);

if (!$credential) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

// 生成一個隨機挑戰
$challenge = random_bytes(32);

// 建立 WebAuthn 註冊選項
$loginOptions = [
    'challenge' => base64_encode($challenge),
    'rp' => [
        'name' => 'KingHold IDP',
        'id' => 'idp.kinghold'
    ],
    'user' => [
        'id' => base64_encode($userId),
        'name' => $userId,
        'displayName' => $displayName
    ],
    'pubKeyCredParams' => [
        [
            'type' => 'public-key',
            'alg' => -7             // ES256 algorithm
        ]
    ],
    'timeout' => 60000,
    'attestation' => 'direct',
    'authenticatorSelection' => [
        'userVerification' => 'preferred'
    ]
];

echo json_encode($loginOptions);