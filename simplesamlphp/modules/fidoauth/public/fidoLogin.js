/*
const axios = require('axios');
const jwt = require('jsonwebtoken');
const net = require('net');
const https = require('https');

const httpsAgent = new https.Agent({
    rejectUnauthorized: false  // Disable SSL verification
});
*/  
var  boxId, boxIp, tokeIat, boxToken, publicKey;
const defaultGatewayIP = "box.pqc";     // "pqc.box"
//const { SignJWT, generateKeyPair } = window.jose;

async function main() {
//  getLocalIPAddress().then(ip => console.log("Local IP:", ip)).catch(console.error);
    try {
        // 從服務器獲取挑戰和其他必要的信息
        const gatewayId = "6CJKyiwZhNV0f+h51vKk4P4WvOFdRmrJclM0rwJGlAZWlwq";
/*
        const response = await fetch('getGatewayPubKey.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ boxId: gatewayId })
        });
        const options = await response.json();
        publicKey = options.publicKey;
        console.log(`publicKey: ${publicKey}`);
//      defaultGatewayIP = "pqc.box";
        defaultGatewayIP = "192.168.30.1";
*/
        getGatewayPublicKey(gatewayId);
/*
        // Step 3: Send getToken API request to the default gateway
        getGatewayToken(defaultGatewayIP);

        const tokenResponse = await axios.get(`https://${defaultGatewayIP}:8080/getToken`);
        boxToken = tokenResponse.data.boxToken;
        console.log(`Token: ${boxToken}`);
        // Decode the token to get box information
        const decodedToken = jwt_decode(boxToken, publicKey);      // jwt.verify(boxToken, publicKey);
        console.log(`Decoded Box Info: ${JSON.stringify(decodedToken)}`);
        boxId= decodedToken.boxId;
        boxIp= decodedToken.ip;
        tokeIat= decodedToken.iat;
*/
    } catch (error) {
        console.error('Error:', error.message);
    }
}

main();

async function getLocalIPAddress() {
    return new Promise((resolve, reject) => {
        const peerConnection = new RTCPeerConnection();
        peerConnection.createDataChannel("");

        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                const candidate = event.candidate.candidate;
                const ipRegex = /([0-9]{1,3}\.){3}[0-9]{1,3}/;
                const ipMatch = candidate.match(ipRegex);
                if (ipMatch) {
                    resolve(ipMatch[0]); // Resolve with the local IP address
                    peerConnection.close();
                }
            }
        };

        peerConnection.createOffer()
            .then(offer => peerConnection.setLocalDescription(offer))
            .catch(reject);
    });
}
    // Function to get the default gateway IP address
async function getDefaultGatewayIP() {
        return new Promise((resolve, reject) => {
        const networkInterfaces = require('os').networkInterfaces();
        for (const interfaceDetails of Object.values(networkInterfaces)) {
            for (const details of interfaceDetails) {
                if (details.family === 'IPv4' && !details.internal && details.netmask !== '255.255.255.255') {
                    // Assume this IP is on the same network as the gateway
                    const ipParts = details.address.split('.');
                    ipParts[3] = '1'; // Typical default gateway on local networks
                    const gatewayIP = ipParts.join('.');
                    if (net.isIP(gatewayIP)) {
                        resolve(gatewayIP);
                        return;
                    }
                }
            }
        }
        reject(new Error('Default gateway IP address not found.'));
    });
}

async function LoginByGet() {
    const username = document.getElementById('user_id').value;
    const form = document.getElementById('loginForm');
    const authState = form.elements['AuthState'].value;

    try {
        // Step 2: Send getToken API request to the default gateway
        getGatewayToken(defaultGatewayIP);
    } catch (error) {
        exit;
    }
    // Step 3: 從後端獲取WebAuthn身份驗證選項
    const optionsResponse = await fetch('getLoginOptions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ username })
    });

    const options = await optionsResponse.json();

    // Step 4: 轉換 challenge 和 user.id 為 ArrayBuffer
    options.challenge = base64urlToArrayBuffer(options.challenge);
    options.allowCredentials.forEach(cred => {
        cred.id = base64urlToArrayBuffer(cred.id);
    });

    // Step 5: 使用 WebAuthn API 讀取 FIDO 憑證
    const credential = await navigator.credentials.get({
        publicKey: options
    });

    // Step 6: 將憑證發送到伺服器進行驗證
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
        // Step 7: 從後端進行資料加簽
        const getSignAuth = await fetch('/api/v1/getSignAuth', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 'boxId':boxId, 'username':username, 'policy': 'admin', 'boxToken': boxToken })
        });
        const signResult = await getSignAuth.json();
        // Step 8: Send Auth pass to PQC gateway
        const toggleRoutingResponse = await axios.post(
            `https://${defaultGatewayIP}:8080/khToggleRouting`,
            { switch: true }, // Body, if needed
            {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${signResult.authorizationHeader}`
                }
            }
        );
        // Step 9: redirect web to zCneter submit login in
        parameters = {
            'c': 'kh',
            'username': username,
            'password': username
        };
        redirectByPost('https://192.168.10.60/launcher/access/submitLogin', parameters, false);
//      window.location.href = 'https://192.168.10.60/launcher/access/submitLogin?c=kh&username='+username;
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

function redirectByPost(url, parameters, inNewTab) {
    // Create a form element
    const form = document.createElement("form");
    form.method = "POST";
    form.action = url;

    // Add each key-value pair as a hidden input field
    for (const key in parameters) {
        if (parameters.hasOwnProperty(key)) {
            const hiddenField = document.createElement("input");
            hiddenField.type = "hidden";
            hiddenField.name = key;
            hiddenField.value = parameters[key];
            form.appendChild(hiddenField);
        }
    }

    // Append form to body and submit
    document.body.appendChild(form);
    form.submit();
}

async function sendAuthToPqcBox(username) {
    // Step 4: Prepare Authorization header and send toggleRouting API request
        const authorizationPayload = {
            exp: boxId,              // Box ID as expiration placeholder
            username: username,
            policy: 'admin',
            boxToken: boxToken
        };

//      const authorizationToken = jwt.sign(authorizationPayload, publicKey, { algorithm: 'ES256' });
        // Run the function to sign the payload
        signAuthorizationPayload().then(({ jwt }) => {
            console.log("JWT:", jwt);
        }).catch(err => console.error("Error:", err));
        authorizationToken = jwt;

        const toggleRoutingResponse = await axios.post(
            `https://${defaultGatewayIP}/khToggleRouting`,
            {}, // Body, if needed
            {
                headers: {
                    'Authorization': `Bearer ${authorizationToken}`
                }
            }
        );

        console.log(`Toggle Routing Response: ${JSON.stringify(toggleRoutingResponse.data)}`);
}

async function signAuthorizationPayload() {
    // 先從後端獲取WebAuthn身份驗證選項
    const getSignAuth = await fetch('/api/v1/getSignAuth', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 'boxId':boxId, 'username':username, 'policy': 'admin', 'boxToken': boxToken })
    });
    const signResult = await getSignAuth.json();
    return {signResult};
}

async function getGatewayPublicKey(gatewayId) {
    try {
        // Solution 1: call server API to read server publickey from database
        const response = await fetch('getGatewayPubKey.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ boxId: gatewayId })
        });
        const options = await response.json();
        publicKey = options.publicKey;
/*
        // Solution 2: Send getPublicKey API request to pqc.box for get publickey
        const publicKeyResponse = await axios.get(`http://pqc.box:8080/getPublicKey`);
        publicKey = publicKeyResponse.data.publicKey;
*/
        console.log(`Public Key: ${publicKey}`);
     } catch (error) {
        console.error('Error:', error.message);
    }
}

function getGatewayToken(gatewayIP) {
    // Step 3: Send getToken API request to the default gateway
    axios.get(`https://${gatewayIP}:8080/getToken`)
        .then (function (tokenResponse) {
            boxToken = tokenResponse.data.boxToken;
            console.log(`Token: ${boxToken}`);

            // Decode the token to get box information
            const decodedToken = jwt_decode(boxToken, publicKey);      // jwt.verify(boxToken, publicKey);
            console.log(`Decoded Box Info: ${JSON.stringify(decodedToken)}`);
            boxId= decodedToken.boxId;
            boxIp= decodedToken.ip;
            tokeIat= decodedToken.iat;

        })
        .catch(function (error) {
            // 失敗時的狀況
            console.log(error);
        })
}
