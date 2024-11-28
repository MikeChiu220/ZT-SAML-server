<?php
// 引入 SimpleSAMLphp 的 Autoload
$path= dirname(__FILE__).'/../../../lib/_autoload.php';
require_once(dirname(__FILE__).'/../../../lib/_autoload.php');
require_once('common.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$displayName = $data['display_name'] ?? $userId;
$credentialInfo = $data['credentialInfo'];

// 這裡應該進行憑證的驗證
// 在實際應用中，你需要驗證 attestation 和其他相關數據

// 假設驗證通過，我們將憑證 ID 保存到數據庫
if (empty($credentialInfo)) {
    echo json_encode(['success' => false, 'error' => 'Invalid credential data']);
    exit;
}

try {
    // 查找或創建新用戶
    $query = $db->prepare('SELECT id FROM users WHERE username = ?');
    $query->execute([$username]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $query = $db->prepare('INSERT INTO users (username, display_name) VALUES (?, ?)');
        $query->execute([$username, $displayName]);
        $userId = $db->lastInsertId();
    } else {
        $query = $db->prepare('UPDATE users SET display_name=? WHERE username=?');
        $query->execute([$displayName, $username]);
        $userId = $user['id'];
    }

    // 提取憑證資料
    if (is_array($credentialInfo['rawId']))
        $credentialId = base64url_encode(implode('', $credentialInfo['rawId']));
    else
        $credentialId = base64url_encode($credentialInfo['rawId']);
    $publicKey = base64url_encode(json_encode($credentialInfo['response'])); // 將公開金鑰等資料儲存
    $signCount = 0; // 初始簽名計數

    // 將憑證存入資料庫
    $query = $db->prepare('INSERT INTO credentials (user_id, credential_id, public_key, sign_count) VALUES (?, ?, ?, ?)');
    $query->execute([$userId, $credentialId, $publicKey, $signCount]);

    http_response_code(200);
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}