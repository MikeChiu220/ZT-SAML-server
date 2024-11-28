const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const path = require('path');
const {
  generateRegistrationOptions,
  generateAuthenticationOptions,
  verifyRegistrationResponse,
  verifyAuthenticationResponse
} = require('@simplewebauthn/server');
const crypto = require('crypto').webcrypto; // 使用Node.js 20的webcrypto

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors()); // 啟用 CORS
app.use(bodyParser.json());
app.use(express.static(path.join(__dirname, 'public'))); // 提供靜態文件

const users = {}; // A simple in-memory store for user data

app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

app.post('/register_public_key', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'register_public_key.php'));
});

app.post('/webauthn/register', async (req, res) => { // 添加 async 關鍵字
  const username = req.body.username;
  const user = users[username] || { credentials: [] };

  // 將 username 轉換為 Uint8Array
  const userID = new TextEncoder().encode(username);

  try {
    const registrationOptions = await generateRegistrationOptions({ // 等待 Promise 完成
      rpName: "Example RP",
      rpID: "localhost",
      userID: userID,
      userName: username,
      attestationType: "none",
      authenticatorSelection: {
        authenticatorAttachment: "cross-platform",
        requireResidentKey: false,
        userVerification: "preferred"
      },
      timeout: 60000,
      excludeCredentials: user.credentials.map(cred => ({
        id: Buffer.from(cred.credentialID, 'base64url'),
        type: 'public-key',
        transports: ['usb', 'ble', 'nfc'],
      })),
    });

    console.log(registrationOptions); // 添加這行來檢查生成的選項
    users[username] = { ...user, challenge: registrationOptions.challenge };
    res.json(registrationOptions);
  } catch (error) {
    console.error(error);
    res.status(500).json({ error: "Failed to generate registration options" });
  }
});

app.post('/webauthn/register/response', async (req, res) => {
  const { username, credential } = req.body;
  const user = users[username];

  if (!credential || !credential.id || !credential.response) {
    return res.status(400).json({ error: "Invalid registration response" });
  }

  try {
    const verification = await verifyRegistrationResponse({
      credential: {
        id: credential.id,
        rawId: Uint8Array.from(credential.rawId),
        type: credential.type,
        response: {
          attestationObject: Uint8Array.from(credential.response.attestationObject),
          clientDataJSON: Uint8Array.from(credential.response.clientDataJSON),
        },
      },
      expectedChallenge: user.challenge,
      expectedOrigin: "http://localhost:3000",
      expectedRPID: "localhost",
    });

    if (verification.verified) {
      user.credentials.push({
        credentialID: credential.id,
        publicKey: verification.registrationInfo.credentialPublicKey,
        counter: verification.registrationInfo.counter,
      });

      users[username] = user;
      res.json({ status: "ok" });
    } else {
      console.error("Registration verification failed:", verification);
      res.status(400).json({ error: "Registration failed" });
    }
  } catch (error) {
    console.error("Error verifying registration response:", error);
    res.status(500).json({ error: "Failed to verify registration response" });
  }
});

app.post('/webauthn/login', async (req, res) => { // 添加 async 關鍵字
  const username = req.body.username;
  const user = users[username];

  if (!user) {
    return res.status(404).json({ error: "User not found" });
  }

  try {
    const authenticationOptions = await generateAuthenticationOptions({ // 等待 Promise 完成
      timeout: 60000,
      allowCredentials: user.credentials.map(cred => ({
        id: Buffer.from(cred.credentialID, 'base64url'),
        type: 'public-key',
        transports: ['usb', 'ble', 'nfc'],
      })),
      userVerification: "preferred",
      rpID: "localhost",
    });

    users[username] = { ...user, challenge: authenticationOptions.challenge };
    res.json(authenticationOptions);
  } catch (error) {
    console.error(error);
    res.status(500).json({ error: "Failed to generate authentication options" });
  }
});

app.post('/webauthn/login/response', async (req, res) => {
  const { username, credential } = req.body;
  const user = users[username];

  try {
    const verification = await verifyAuthenticationResponse({
      credential,
      expectedChallenge: user.challenge,
      expectedOrigin: "http://localhost:3000",
      expectedRPID: "localhost",
      authenticator: user.credentials.find(cred => cred.credentialID === credential.id),
    });

    if (verification.verified) {
      user.credentials = user.credentials.map(cred => {
        if (cred.credentialID === credential.id) {
          cred.counter = verification.authenticationInfo.newCounter;
        }
        return cred;
      });

      res.json({ status: "ok" });
    } else {
      res.status(400).json({ error: "Authentication failed" });
    }
  } catch (error) {
    console.error(error);
    res.status(500).json({ error: "Failed to verify authentication response" });
  }
});

app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});
