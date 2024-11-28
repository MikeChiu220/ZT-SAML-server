async function registerFIDO() {
    const username = document.getElementById('user_id').value;
    const display_name = document.getElementById('display_name').value;
    // Call this function when the page loads
    checkFidoSupport().then(supported => {
        if (!supported) {
            alert('FIDO authentication is not supported on this device or browser.');
        }
    });
    if (!username) {
        alert('Please enter a User ID');
        return;
    }
    if (!display_name)
        display_name = username;

    try {
        // 從服務器獲取挑戰和其他必要的信息
        const response = await fetch('get_registration_options.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username: username, display_name: display_name })
        });
        const options = await response.json();

        // 轉換 challenge 和 user.id 為 ArrayBuffer
        options.challenge = base64urlToArrayBuffer(options.challenge);
        options.user.id = base64urlToArrayBuffer(options.user.id);

        // 使用 WebAuthn API 創建新的憑證
        const credential = await navigator.credentials.create({
                publicKey: options
          });

        // 準備發送到服務器的數據
        const credentialData = {
            username: username,
            display_name: display_name,
            credentialInfo: {
                id: credential.id,
                rawId: arrayBufferToBase64url(credential.rawId),        // Array.from(new Uint8Array(credential.rawId)),
                type: credential.type,
                response: {
                    attestationObject: arrayBufferToBase64url(credential.response.attestationObject),   // Array.from(new Uint8Array(credential.response.attestationObject)),
                    clientDataJSON: arrayBufferToBase64url(credential.response.clientDataJSON)          // Array.from(new Uint8Array(credential.response.clientDataJSON))
                }
            }
        };

        // 發送憑證數據到服務器進行驗證和保存
        const saveResponse = await fetch('save_credential.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(credentialData)
        });

        const result = await saveResponse.json();
        if (result.success) {
            alert( username+' FIDO 卡片註冊成功!');
            document.getElementById('user_id').value = '';
            document.getElementById('display_name').value = '';
        } else {
            alert('Failed to register FIDO credential: ' + result.error);
        }

    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred during registration.');
    }
}

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

// 輔助函數：將 ArrayBuffer 轉換為 base64url
function arrayBufferToBase64url(buffer) {
    const base64 = btoa(String.fromCharCode.apply(null, new Uint8Array(buffer)));
    return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
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

