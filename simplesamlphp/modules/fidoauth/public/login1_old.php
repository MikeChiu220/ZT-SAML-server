<?php

$path= dirname(__FILE__).'/../../../lib/_autoload.php';
require_once(dirname(__FILE__).'/../../../lib/_autoload.php');

$authStateId = $_REQUEST['AuthState'] ?? null;

if ($authStateId === null) {
    throw new \SimpleSAML\Error\BadRequest('Missing AuthState parameter.');
}

try {
    $state = \SimpleSAML\Auth\State::loadState($authStateId, 'fidoauth:AuthState');
} catch (\Exception $e) {
    throw new \SimpleSAML\Error\BadRequest('Invalid AuthState parameter.');
}

$sourceId = $state['\SimpleSAML\Auth\Source.id'] ?? null;

if ($sourceId === null) {
    throw new \SimpleSAML\Error\BadRequest('Missing AuthSource in state.');
}

try {
    $source = \SimpleSAML\Auth\Source::getById($sourceId);
    if (!($source instanceof \SimpleSAML\Module\fidoauth\Auth\Source\Fido)) {
        throw new \SimpleSAML\Error\BadRequest('Invalid AuthSource type.');
    }
} catch (\Exception $e) {
    throw new \SimpleSAML\Error\BadRequest('Invalid AuthSource: ' . $e->getMessage());
}

$challenge = $source->getAuthenticationChallenge();

$config = \SimpleSAML\Configuration::getInstance();

// Start output
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FIDO Authentication</title>
</head>
<body>
    <h2>FIDO Authentication</h2>
    <p>Please authenticate using your FIDO device.</p>

    <form id="loginForm">
        <label for="username">Username:</label>
        <input type="hidden" name="AuthState" value="<?php echo htmlspecialchars($authStateId); ?>" />
        <input type="hidden" name="challenge" value="<?php echo htmlspecialchars(json_encode(['challenge' => base64_encode($challenge->getChallenge())])); ?>" />
        <input type="text" id="username" name="username" required>
        <button type="button" onclick="LoginByGet()">Login by get</button>
        </form>

    <div id="debug-info" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc;">
        <h3>Debug Information:</h3>
        <p>Current URL: <span id="current-url"></span></p>
        <p>Hostname: <span id="hostname"></span></p>
        <p>Protocol: <span id="protocol"></span></p>
        <p>Challenge: <pre id="challenge-debug"></pre></p>
    </div>

    <div id="wait-message" style="display: none;">
        <p>Waiting for FIDO Authentication...</p>
    </div>

    <div id="fido-info" style="display: none;">
        <h3>FIDO Authentication Information</h3>
        <pre id="fido-info-content"></pre>
    </div>

    <script>
    // Show Debug information
    document.getElementById('current-url').textContent = window.location.href;
    document.getElementById('hostname').textContent = window.location.hostname;
    document.getElementById('protocol').textContent = window.location.protocol;
    document.getElementById('challenge-debug').textContent = document.getElementById('loginForm').elements['challenge'].value;

    async function LoginByGet() {
        const username = document.getElementById('username').value;
        const form = document.getElementById('loginForm');
        const authState = form.elements['AuthState'].value;
        const challengeRaw = form.elements['challenge'].value;
        let challengeObj = JSON.parse(challengeRaw);
        const challenge = challengeObj.challenge;

        // 先從後端獲取WebAuthn身份驗證選項
        const optionsResponse = await fetch('getLoginOptions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, challenge })
        });

        const options = await optionsResponse.json();

        // 轉換 challenge 和 user.id 為 ArrayBuffer
        options.challenge = base64urlToArrayBuffer(options.challenge);
        options.allowCredentials.forEach(cred => {
            cred.id = base64urlToArrayBuffer(cred.id);
        });

        // 使用 WebAuthn API 讀取 FIDO 憑證
        const credential = await navigator.credentials.get({
            publicKey: options
        });

        // 將憑證發送到伺服器進行驗證
        const verifyResponse = await fetch('verifyLogin.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                username,
                authState,
                credential: {
                    id: credential.id,
                    rawId: arrayBufferToBase64url(credential.rawId),
                    type: credential.type,
                    response: {
                        authenticatorData: arrayBufferToBase64url(credential.response.authenticatorData),
                        clientDataJSON: arrayBufferToBase64url(credential.response.clientDataJSON),
                        signature: arrayBufferToBase64url(credential.response.signature),
                        userHandle: credential.response.userHandle ? arrayBufferToBase64url(credential.response.userHandle) : null
                    }
                }
            })
        });

        if (verifyResponse.ok) {
            window.location.href = 'success.php?AuthState='+authState;
            alert('Login successful!');
        } else {
            alert('Login failed!');
        }
    }

    // Add this function to check FIDO support
    async function checkFidoSupport() {
        if (!window.PublicKeyCredential) {
            console.log('WebAuthn is not supported in this browser.');
            return false;
        }

        try {
            const available = await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
            console.log('FIDO Authenticator available:', available);
            return available;
        } catch (error) {
            console.error('Error checking FIDO support:', error);
            return false;
        }
    }

    // Call this function when the page loads
    checkFidoSupport().then(supported => {
        if (!supported) {
            alert('FIDO authentication is not supported on this device or browser.');
        }
    });
    // 輔助函數：將 base64url 轉換為 ArrayBuffer
    function base64urlToArrayBuffer(base64url) {
        const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
        const binaryString = window.atob(base64);
        const len = binaryString.length;
        const bytes = new Uint8Array(len);
        for (let i = 0; i < len; i++) {
            bytes[i] = binaryString.charCodeAt(i);
        }
        return bytes.buffer;
    }
    function base64urlToUint8Array(base64URL) {
        const inputStr = base64URL;
        const padding = '='.repeat((4 - base64URL.length % 4) % 4);
        const base64 = (base64URL + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // 輔助函數：將 ArrayBuffer 轉換為 base64url
    function arrayBufferToBase64url(buffer) {
        const base64 = btoa(String.fromCharCode.apply(null, new Uint8Array(buffer)));
        return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
    }
    function bufferToBase64Url(buffer) {
        const base64 = btoa(String.fromCharCode.apply(null, new Uint8Array(buffer)));
        return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
    }

    </script>
</body>
</html>