<?php
session_start();
// Retrieve the stored challenge and rpId from the session or database
$expectedChallenge = $_SESSION['challenge'];
$publicKey = $_SESSION['public_key'];

// 引入 SimpleSAMLphp 的 Autoload
$path= dirname(__FILE__).'/../../../lib/_autoload.php';
require_once(dirname(__FILE__).'/../../../lib/_autoload.php');
require_once('common.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$loginUserName = $data['username'] ?? '';
$credentialId = $data['credential']['id'] ?? '';
$clientDataJSON = $data['credential']['response']['clientDataJSON'];
$clientDataJSON = base64url_decode($data['credential']['response']['clientDataJSON']);
$authenticatorData = base64url_decode($data['credential']['response']['authenticatorData']);
$signature = base64url_decode($data['credential']['response']['signature']);
$rawId = base64url_decode($data['credential']['rawId']);
$authStateId = $data['authState'] ?? '';echo "authStateId=$authStateId\n";
$state = \SimpleSAML\Auth\State::loadState($authStateId, 'fidoauth:AuthState');

// Step 1: 解析 clientDataJSON
$clientData = json_decode($clientDataJSON, true);
if ($clientData === null) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid clientDataJSON']);
    exit;
}
// 驗證挑戰
// Remove padding from both challenges for comparison
$expectedChallenge = rtrim($expectedChallenge, '=');
$clientChallenge = rtrim($clientData['challenge'], '=');
if ($clientChallenge !== $expectedChallenge) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Challenge does not match']);
    exit;
}
// 驗證操作類型 (必須是 "webauthn.get")
if ($clientData['type'] !== 'webauthn.get') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid operation type']);
    exit;
}
// 驗證來源
/*
if ($clientData['origin'] !== 'https://' . $rpId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Origin does not match']);
    exit;
}

// Step 2: 解析 authenticatorData
// RP ID Hash is the first 32 bytes
$rpIdHash = substr($authenticatorData, 0, 32);
$flags = ord($authenticatorData[32]); // 33rd byte is flags
$signCount = unpack("N", substr($authenticatorData, 33, 4))[1]; // 34th to 37th byte is the signature count

// 驗證 RP ID Hash
$expectedRpIdHash = hash('sha256', $rpId, true);
if ($rpIdHash !== $expectedRpIdHash) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'RP ID hash does not match']);
    exit;
    die('RP ID hash does not match');
}

// Step 3: 構建簽名資料
// 1. Concatenate authenticatorData and clientDataHash
$clientDataHash = hash('sha256', $clientDataJSON, true);
$signedData = $authenticatorData . $clientDataHash;

// Step 4: 驗證簽名
// 使用 OpenSSL 驗證簽名 (公鑰是 PEM 格式)
$pubKey = openssl_pkey_get_public($publicKey);
if ($pubKey === false) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid public key']);
    exit;
}

$verify = openssl_verify($signedData, $signature, $pubKey, OPENSSL_ALGO_SHA256);
if ($verify === 1) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Signature is valid!']);
    exit;
} elseif ($verify === 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Signature is invalid!']);
    exit;
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Error during signature verification: ' . openssl_error_string()]);
    exit;
}

if (empty($credentialId)) {
    echo json_encode(['success' => false, 'message' => 'No credential ID provided']);
}
else {
*/
    // 搜索匹配的憑證
    $stmt = $db->prepare("SELECT * FROM credentials WHERE credential_id = :credentialId");
    $stmt->execute(['credentialId' => $credentialId]);
    $credential = $stmt->fetch(PDO::FETCH_ASSOC);

//  if ($credential) {
        // 找到匹配的憑證
        // Continue with the authentication process
        $loginAttributes = [];
        $loginAttributes['uid'][0] = $loginUserName;
        $state['Attributes'] = $loginAttributes;
        $state['fidoauth:authenticated'] = true;
        $stateId = \SimpleSAML\Auth\State::saveState($state, 'fidoauth:AuthState');
//        SimpleSAML\Auth\Source::loginCompleted($state);
        exit;
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Credential found']);
/*
    } else {
        // 未找到匹配的憑證
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Credential not found']);
    }    
}
*/
