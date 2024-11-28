<?php
session_start();
// 引入 SimpleSAMLphp 的 Autoload
$path= dirname(__FILE__).'/../../../lib/_autoload.php';
require_once(dirname(__FILE__).'/../../../lib/_autoload.php');
require_once('common.php');

// 接收前端傳來的使用者資訊
$data = json_decode(file_get_contents('php://input'), true);
$boxId = $data['boxId'];

// 生成一個隨機的 challenge
//$challenge = base64url_encode(random_bytes(32));

// 查找用戶的已註冊憑證
$query = $db->prepare('SELECT gatewayIp, publicKey FROM gateway_Info WHERE deviceId = ?');
$query->execute([$boxId]);
$options = [];
if ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    // 建立 WebAuthn 登入選項
    $options = [
        'gatewayIp' => $row['gatewayIp'],
        'publicKey' => $row['publicKey']
    ];
}

header('Content-Type: application/json');
echo json_encode($options);
