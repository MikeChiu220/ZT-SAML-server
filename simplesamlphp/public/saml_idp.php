<?php
require_once('../vendor/autoload.php');

SimpleSAML\Configuration::setConfigDir('../config');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the metadata storage handler for Get the IdP entity ID
$metadataHandler = \SimpleSAML\Metadata\MetaDataStorageHandler::getMetadataHandler();
$idpEntityId = $metadataHandler->getMetaDataCurrentEntityID('saml20-idp-hosted');

// Handle SAML 2.0 SP-initiated SSO
$idp = SimpleSAML\IdP::getById('saml2:' . $idpEntityId);
$idpMetadata = $idp->getConfig();
$authSource = $idpMetadata->getString('auth');

$session = SimpleSAML\Session::getSessionFromRequest();

// Check if we have a SAML request
if (isset($_REQUEST['SAMLRequest'])) {
    try {
        $binding = new \SAML2\HTTPRedirect();
        $samlRequest = $binding->receive();
        
        if (!($samlRequest instanceof \SAML2\AuthnRequest)) {
            throw new Exception('Invalid SAML authentication request received.');
        }
        // 獲取 AuthnContextClassRef
        $requestedAuthnContext = $samlRequest->getRequestedAuthnContext();
        $authnContextClassRef = null;
        if ($requestedAuthnContext !== null) {
            $authnContextClassRefs = $requestedAuthnContext['AuthnContextClassRef'];
            if (!empty($authnContextClassRefs)) {
                $authnContextClassRef = $authnContextClassRefs[0];
            }
        }

        // 根據 AuthnContextClassRef 決定認證方法
        switch ($authnContextClassRef) {
            case 'urn:oasis:names:tc:SAML:2.0:ac:classes:Password':
                $authSource = 'example-userpass'; // 假設這是您的密碼認證源
                break;
            case 'urn:oasis:names:tc:SAML:2.0:ac:classes:AuthenticationContextDeclaration':
                $authSource = 'fido-auth'; // 假設這是您的 FIDO 認證源
                break;
            // 可以根據需要添加更多的 case
        }
        $authSource = 'fido-auth';

        // Store the request in the session
        $session->setData('saml', 'samlrequest', base64_encode(serialize($samlRequest)));
        $session->setData('saml', 'RelayState', $_REQUEST['RelayState'] ?? null);
        
        // Debug output
        error_log("SAML Request stored in session: " . print_r($samlRequest, true));
    } catch (Exception $e) {
        error_log("Error decoding SAML request: " . $e->getMessage());
        throw new SimpleSAML\Error\BadRequest('Error decoding SAML request: ' . $e->getMessage());
    }
}

// Initialize the authentication source
$as = new SimpleSAML\Auth\Simple($authSource);

// At this point, the user is authenticated
$attributes = $as->getAttributes();

// Retrieve the stored SAML request
$storedRequest = $session->getData('saml', 'samlrequest');
$relayState = $session->getData('saml', 'RelayState');

// Debug output
error_log("Stored SAML Request: " . print_r($storedRequest, true));

if ($storedRequest !== null) {
    try {
        $samlRequest = unserialize(base64_decode($storedRequest));
        
        if (!($samlRequest instanceof \SAML2\AuthnRequest)) {
            error_log("Stored message wasn't an authentication request. Type: " . gettype($samlRequest));
            throw new SimpleSAML\Error\BadRequest('Stored message wasn\'t an authentication request.');
        }

        // 獲取 SP 的 entityID
        $issuer = $samlRequest->getIssuer();
        // 提取 entityID
        if (is_array($issuer) && isset($issuer['value'])) {
            $spEntityId = $issuer['value'];
        } elseif (is_object($issuer) && method_exists($issuer, 'getValue')) {
            $spEntityId = $issuer->getValue();
        } else {
            $spEntityId = (string) $issuer;
        }
        // 獲取 RequestId
        $requestId = $samlRequest->getId();
        // 獲取 AssertionConsumerServiceURL
        $consumerURL = $samlRequest->getAssertionConsumerServiceURL();
        // 獲取綁定類型
        $binding = $samlRequest->getProtocolBinding();
        if (empty($binding)) {
            // 如果請求中沒有指定綁定，使用默認的 HTTP-POST 綁定
            $binding = \SAML2\Constants::BINDING_HTTP_POST;
        }
        $state = [
            'Responder' => ['\SimpleSAML\Module\saml\IdP\SAML2', 'sendResponse'],
            'saml:sp:IdP' => $idpEntityId,
            'saml:sp:AuthnRequest' => $samlRequest,
            'saml:RelayState' => $relayState,
            'Attributes' => $attributes,
            'SPMetadata' => ['entityid' => $spEntityId],    // 添加 SP entityID
            'saml:RequestId' => $requestId,                 // 添加 RequestId
            'saml:ConsumerURL' => $consumerURL,             // 添加 ConsumerURL
            'saml:Binding' => $binding,                     // 添加綁定信息
        ];

        // 如果有指定 AssertionConsumerServiceURL，添加到狀態中
        if ($samlRequest->getAssertionConsumerServiceURL() !== null) {
            $state['saml:AssertionConsumerServiceURL'] = $samlRequest->getAssertionConsumerServiceURL();
        }

        // If not authenticated, start the authentication process
        if (!$as->isAuthenticated()) {
            // 開始認證過程
            $authSource = new \SimpleSAML\Auth\Simple($authSource);
            $authSource->login($state);
        }
        else {
            // 認證完成後，控制權會返回到 Responder（在這個例子中是 sendResponse 方法）
            $idp->handleAuthenticationRequest($state);
        }
    } catch (Exception $e) {
        error_log("Error processing stored SAML request: " . $e->getMessage());
        throw new SimpleSAML\Error\BadRequest('Error processing stored SAML request: ' . $e->getMessage());
    }
} else {
    // No stored SAML request, direct do FIDO Login
    $fidoLoginURL = "https://idp.kinghold/samlIdp/module.php/fidoauth/fidoLogin.php";
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $fidoLoginURL");
    exit;
/*
    // No stored SAML request, show attributes or login link
    echo "<h2>Authenticated User Attributes:</h2>";
    echo "<pre>" . print_r($attributes, true) . "</pre>";
    echo "<a href='?logout'>Logout</a>";
*/
}

// Handle logout action
if (isset($_GET['logout'])) {
    $as->logout();
}