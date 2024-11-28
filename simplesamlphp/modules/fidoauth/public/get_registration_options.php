<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$displayName = $data['displayName'] ?? $username;

if (empty($username)) {
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

// 生成一個隨機挑戰
$challenge = random_bytes(32);

// 建立 WebAuthn 註冊選項
$registrationOptions = [
    'challenge' => base64_encode($challenge),
    'rp' => [
        'name' => 'KingHold IDP',
        'id' => 'idp.kinghold'
    ],
    'user' => [
        'id' => base64_encode($username),
        'name' => $username,
        'displayName' => $displayName
    ],
    'pubKeyCredParams' => [
        [
            'type' => 'public-key',
            'alg' => -7             // ES256 algorithm
        ]
    ],
    'timeout' => 60000,
    'attestation' => 'direct'
];

echo json_encode($registrationOptions);