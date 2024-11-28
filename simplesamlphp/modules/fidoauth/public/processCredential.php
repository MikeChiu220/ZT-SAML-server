<?php
// 引入 SimpleSAMLphp 的 Autoload
$path= dirname(__FILE__).'/../../../lib/_autoload.php';
require_once(dirname(__FILE__).'/../../../lib/_autoload.php');
require_once('common.php');

// 接收前端傳送過來的憑證數據
$data = json_decode(file_get_contents('php://input'), true);

// 提取憑證 ID
$credentialId = $data['id'];
$clientDataJSON = base64url_decode($data['response']['clientDataJSON']);
$attestationObject = base64url_decode($data['response']['attestationObject']);

// 搜尋資料庫中是否有匹配的憑證
$query = $db->prepare('SELECT * FROM credentials WHERE credential_id = ?');
$decodeCredentialId = base64_decode($credentialId);
$query->execute([$decodeCredentialId]);
$credential = $query->fetch(PDO::FETCH_ASSOC);

if ($credential) {
    // 如果找到匹配的憑證，進行驗證處理
    echo json_encode(['success' => true, 'message' => 'Credential found and verified']);
} else {
    // 沒有找到憑證，可能需要創建新的
    echo json_encode(['success' => false, 'message' => 'Credential not found']);
}
