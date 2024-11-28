<?php

$path= dirname(__FILE__).'../../../lib/_autoload.php';
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
    <script src="https://unpkg.com/@simplewebauthn/browser/dist/bundle/index.umd.min.js"></script>
</head>
<body>
    <h2>FIDO Authentication</h2>
    <p>Please authenticate using your FIDO device.</p>

    <form id="fido-form">
        <input type="hidden" name="AuthState" value="<?php echo htmlspecialchars($authStateId); ?>" />
        <input type="hidden" name="challenge" value="<?php echo htmlspecialchars(json_encode(['challenge' => base64_encode($challenge->getChallenge())])); ?>" />
        <button type="button" onclick="startAuthentication()">Authenticate with FIDO</button>
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
    document.getElementById('current-url').textContent = window.location.href;
    document.getElementById('hostname').textContent = window.location.hostname;
    document.getElementById('protocol').textContent = window.location.protocol;
    document.getElementById('challenge-debug').textContent = document.getElementById('fido-form').elements['challenge'].value;

    async function startAuthentication() {
        const form = document.getElementById('fido-form');
        const authState = form.elements['AuthState'].value;
        const challengeRaw = form.elements['challenge'].value;

        console.log('Raw challenge:', challengeRaw);

        document.getElementById('wait-message').style.display = 'block';

        try {
            const challengeObj = JSON.parse(challengeRaw);
            if (!challengeObj.challenge) {
                throw new Error('Challenge not found in parsed object');
            }

            const options = {
                challenge: challengeObj.challenge,
                timeout: 60000,
                rpId: window.location.hostname,
                userVerification: "preferred"
            };

            console.log('Authentication options:', options);

            const authResult = await SimpleWebAuthnBrowser.startAuthentication(options);

            document.getElementById('wait-message').style.display = 'none';
            document.getElementById('fido-info').style.display = 'block';
            document.getElementById('fido-info-content').textContent = JSON.stringify(authResult, null, 2);

            // Send authentication result to server
            const response = await fetch('verify.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    AuthState: authState,
                    fidoResponse: authResult
                }),
            });

            if (response.ok) {
                window.location.href = 'success.php';
            } else {
                console.error('Authentication failed');
                alert('Authentication failed. Please try again.');
            }
        } catch (error) {
            console.error('Error during authentication:', error);
            document.getElementById('wait-message').style.display = 'none';
            alert('Error during authentication: ' + error.message);
        }
    }

    // Check if WebAuthn is supported
    if (SimpleWebAuthnBrowser.browserSupportsWebAuthn()) {
        console.log('WebAuthn is supported in this browser.');
    } else {
        console.log('WebAuthn is not supported in this browser.');
        alert('WebAuthn is not supported in this browser. Please use a modern browser that supports WebAuthn.');
    }
    </script>
</body>
</html>