<?php
require_once('../lib/_autoload.php');

// 啟用錯誤報告以便調試
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 初始化 SimpleSAMLphp 配置
$config = \SimpleSAML\Configuration::getInstance();
$metadata = \SimpleSAML\Metadata\MetaDataStorageHandler::getMetadataHandler();

// 獲取默認的 IdP
try {
    $idpEntityId = $metadata->getMetaDataCurrentEntityID('saml20-idp-hosted');
    $idpMetadata = $metadata->getMetaDataConfig($idpEntityId, 'saml20-idp-hosted');
} catch (\Exception $e) {
    error_log("Error retrieving IdP metadata: " . $e->getMessage());
    echo "Error: Unable to retrieve IdP configuration.";
    exit;
}

if (empty($idpEntityId)) {
    error_log("Error: Could not retrieve 'entityid' from IdP configuration.");
    echo "Error: IdP entityID is not properly configured.";
    exit;
}

// 初始化 IdP
try {
    $idp = \SimpleSAML\IdP::getById('saml2:' . $idpEntityId);
} catch (\Exception $e) {
    error_log("Error initializing IdP: " . $e->getMessage());
    echo "Error initializing IdP. Please check your configuration.";
    exit;
}

try {
    if (isset($_REQUEST['SAMLRequest'])) {
        // 接收 SAML 登出請求
        $binding = \SAML2\Binding::getCurrentBinding();
        $request = $binding->receive();

        if (!($request instanceof \SAML2\LogoutRequest)) {
            throw new Exception('Received message is not a LogoutRequest.');
        }

        // 獲取發送請求的 SP 的 entityID
        $spEntityId = $request->getIssuer();
        // 確保 $spEntityId 是一個字符串
        if ($spEntityId instanceof \SAML2\XML\saml\Issuer) {
            $spEntityId = $spEntityId->getValue();
        } elseif (is_object($spEntityId) && method_exists($spEntityId, '__toString')) {
            $spEntityId = (string)$spEntityId;
        } elseif (!is_string($spEntityId)) {
            throw new Exception('Invalid SP EntityID format');
        }

        // 獲取會話
        $session = \SimpleSAML\Session::getSessionFromRequest();

        // 嘗試找到與此 SP 相關聯的 ID
        $assocId = null;
        $sessionIndex = $request->getSessionIndex();
        if ($sessionIndex !== null) {
            $assocId = $session->getAuthority($sessionIndex);
        }

        if ($assocId === null) {
            // 如果無法通過 SessionIndex 找到，嘗試遍歷所有認證源
            $authSources = $session->getAuthorities();
            foreach ($authSources as $authority) {
                $data = $session->getAuthData($authority, 'saml:sp:IdP');
                if ($data === $idpEntityId) {
                    $nameId = $session->getAuthData($authority, 'saml:sp:NameID');
                    if ($nameId['Value'] === $request->getNameId()->getValue()) {
                        $assocId = $authority;
                        break;
                    }
                }
            }
        }

        // 處理登出請求
        $state = [
            'Responder' => ['\SimpleSAML\Module\saml\IdP\SAML2', 'sendLogoutResponse'],
            'saml:SPEntityId' => $spEntityId,  // 現在這裡保證是一個字符串
            'saml:RelayState' => $request->getRelayState(),
            'saml:RequestId' => $request->getId(),
        ];

        // 執行單點登出
        $idp->handleLogoutRequest($state, $assocId);
    } else {
        // 如果沒有收到 SAML 請求，顯示錯誤信息
        throw new Exception('No SAML LogoutRequest received.');
    }
} catch (Exception $e) {
    // 錯誤處理
    $error = $e->getMessage();
    error_log("Error processing logout request: " . $error);
    echo "Error: " . htmlspecialchars($error);
}

// 如果執行到這裡，說明登出成功
echo "Logout successful.";