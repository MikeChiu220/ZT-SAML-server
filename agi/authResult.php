<?php
include("include/connection.php");//Mysql連結

require_once 'vendor/autoload.php'; // Assuming you are using Composer for xmlseclibs
require_once 'vendor/robrichards/xmlseclibs/xmlseclibs.php';
// 使用 XMLSecLibs 庫來生成 SAML 簽章
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
    
// authResult.php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$privateKeyFile = 'cert/serverT1.key';
$publicCertFile = 'cert/serverT1.crt';

global $privateKeyFile, $publicCertFile;

/**
 * @OA\Get(
 *   tags={"xTrust", "authResult"},
 *   path="/api/authResult",
 *   summary="Process xTrust send user auth result",
 *   @OA\Parameter(ref="#/components/parameters/id"),
 *   @OA\Response(response=200, description="OK"),
 *   @OA\Response(response=401, description="Unauthorized"),
 *   @OA\Response(response=404, description="Not Found")
 * )
 */
class ZTA {

    public function verifyToken($jweToken, $identity_pubkey_path, $rp_prikey_path, $rpKeyLibPas, $clientIp, $userId, $rpId) {
		// ---- { Mike Temp for test
		$result["valid"] ="true";
		$result["clientip"] = $clientIp;
		$result["trans_id"] = "3142ccb3-1d74-469d-9a53-32293d3fd8c1";
		$result["fail_reason"] = "";
		$result["rpid"] = $rpId;
		$result["userid"] = $userId;
		$result["lat"] = "1677046990";
		$result["expired time"] = "1677048771";
//		$result = $this->verifyJWEToken($jweToken, $identity_pubkey_path, $rp_prikey_path, $rpKeyLibPas, $clientIp, $userId, $rpId);
		// ---- }
        return json_encode($result);
    }

    private function verifyJWEToken($jweToken, $identity_pubkey_path, $rp_prikey_path, $rpKeyLibPas, $clientIp, $userId, $rpId) {
        $result = [];

        $privateKey = $this->getRSAPrivateKey($rp_prikey_path, $rpKeyLibPas);
        $publicKey = $this->getECPublicKey($identity_pubkey_path);

        if (!$privateKey) {
            $result['valid'] = 'false';
            $result['fail_reason'] = "Loading RP server's privateKey failed; key_path: $rp_prikey_path";
            return $result;
        }

        if (!$publicKey) {
            $result['valid'] = 'false';
            $result['fail_reason'] = "Loading identity server's publicKey failed; key_path: $identity_pubkey_path";
            return $result;
        }

        try {
            // Assuming $jweToken is already decrypted and contains the JWS
            $jws = $this->decryptJWE($jweToken, $privateKey);

            // Verify JWS Token
            $result = $this->verifyJWSToken($jws, $publicKey, $clientIp, $userId, $rpId);
            return $result;

        } catch (Exception $e) {
            $result['valid'] = 'false';
            $result['fail_reason'] = $e->getMessage();
            return $result;
        }
    }

    private function verifyJWSToken($jws_token, $pubkey, $clientIp, $userId, $rpId) {
        $result = [];
        try {
            $decoded = JWT::decode($jws_token, new Key($pubkey, 'ES256'));

            if ($decoded->clientip !== $clientIp) {
                $result['valid'] = 'false';
                $result['fail_reason'] = 'clientIp does not match';
                return $result;
            }

            if (strtolower($decoded->userid) !== strtolower($userId)) {
                $result['valid'] = 'false';
                $result['fail_reason'] = 'userId does not match';
                return $result;
            }

            if ($decoded->rpid !== $rpId) {
                $result['valid'] = 'false';
                $result['fail_reason'] = 'rpid does not match';
            }

            $now = new DateTimeImmutable();
            $nbf = new DateTimeImmutable('@' . $decoded->nbf);
            $exp = new DateTimeImmutable('@' . $decoded->exp);

            if ($now < $nbf || $now > $exp) {
                $result['valid'] = 'false';
                $result['fail_reason'] = 'JWT token is expired or not yet valid';
            } else {
                $result['valid'] = 'true';
            }

            $result['clientip'] = $decoded->clientip;
            $result['userid'] = $decoded->userid;
            $result['rpid'] = $decoded->rpid;
            $result['trans_id'] = $decoded->jti;

        } catch (Exception $e) {
            $result['valid'] = 'false';
            $result['fail_reason'] = $e->getMessage();
        }

        return $result;
    }

    private function getRSAPrivateKey($file_path, $rpKeyLibPas) {
        $content = file_get_contents($file_path);
        $privateKey = openssl_pkey_get_private($content, $rpKeyLibPas);

        if (!$privateKey) {
            throw new Exception("Failed to load RSA private key from $file_path");
        }

        return $privateKey;
    }

    private function getECPublicKey($file_path) {
        $content = file_get_contents($file_path);
        $publicKey = openssl_pkey_get_public($content);

        if (!$publicKey) {
            throw new Exception("Failed to load EC public key from $file_path");
        }

        return $publicKey;
    }

    private function decryptJWE($jweToken, $privateKey) {
        // Placeholder for decrypting JWE string to get the JWS
        // This would involve specific libraries or OpenSSL calls to decrypt the JWE
        // Return the JWS string after decryption
        return $jws;
    }
}

if ( !file_exists($privateKeyFile) ) {
    // Usage
    $keys = generateSAMLKeys(
        "TW",
        "Taiwan",
        "New Taipei City",
        "KingHold",
        "kinghold.com.tw",
        "admin@kinghold.com.tw"
    );

    // Save private key
    file_put_contents($privateKeyFile, $keys['privateKey']);

    // Save certificate (public key)
    file_put_contents($publicCertFile, $keys['certificate']);

    error_log( "Private key and certificate have been generated and saved." );
}

// At the beginning of the file, add some debug output:
error_log("Private key content: " . file_get_contents($privateKeyFile));
error_log("Public cert content: " . file_get_contents($publicCertFile));

session_start();
$samlId = $_SESSION['samlId']??NULL;
$reqIssuer = $_SESSION['reqIssuer']??NULL;
$destination = $_SESSION['destination']??NULL;
$assertionConsumerServiceURL = $_SESSION['assertionConsumerServiceURL']??NULL;
$clientIp = $_SERVER['HTTP_X_REAL_IP']??"192.168.174.1";	// 客戶端的ip 位址，來源為閘道端header，X-Real-IP。
//print_r($_SERVER);

if ( !isset($samlId) ) {
	$command = "SELECT samlId,reqIssuer,destination,assertionConsumerServiceURL FROM loginSession WHERE gatewayIp='$clientIp' OR clientIp='$clientIp'";
//	echo "$command";
	$qryResult = FUN_SQL_QUERY($command, $database);
	$total_record = FUN_SQL_NUM_ROWS($qryResult);	//$ 總筆數
//	echo " -> $total_record<br>\n";
	if ( $total_record ) {
		$row = FUN_SQL_FETCH_ARRAY( $qryResult);
		$samlId						=$row['samlId'];
		$reqIssuer					=$row['reqIssuer'];
		$destination				=$row['destination'];
		$assertionConsumerServiceURL=$row['assertionConsumerServiceURL'];
	}
}
if (!isset($samlId) || !isset($destination) || !isset($assertionConsumerServiceURL)) {
    http_response_code(400);
	if (!isset($samlId)) {
		echo "SAML session: samlId missing.<br>";
	}
	if (!isset($destination)) {
		echo "SAML session: destination missing.<br>";
	}
	if (!isset($assertionConsumerServiceURL)) {
		echo "SAML session: assertionConsumerServiceURL missing.<br>";
	}
    exit;
}
else {
/*
	echo "samlId = ".$samlId.".<br>\n";
	echo "destination = ".$destination.".<br>\n";
	echo "reqIssuer = ".$reqIssuer.".<br>\n";
	echo "assertionConsumerServiceURL = ".$assertionConsumerServiceURL.".<br>\n";
*/
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {	// Mike Temp Del for test		 && isset($_SERVER['HTTP_X_ZTA_AUTH_TOKEN'])
    $zta = new ZTA();
    $jweToken = $_SERVER['HTTP_X_ZTA_AUTH_TOKEN']??"";		// 加密的JWE token，來源為閘道端header，X-ZTA-AUTH-TOKEN。
    $clientIp = $_SERVER['HTTP_X_REAL_IP']??"";				// 客戶端的ip 位址，來源為閘道端header，X-Real-IP。
    $userId   = $_SERVER['HTTP_X-ZTA-AUTH-USER']??"";		// 使用者名稱，來源為閘道端header，X-ZTA-AUTH-USER。
    $rpId     = $_SERVER['HTTP_X-ZTA-AUTH-RPID']??"";		// RP 伺服器端的名稱，來源為閘道端header，X-ZTA-AUTH-RPID。
	$identity_pubkey_path = "/cert/idp_cert.pem";			// 解密JWS 用的公鑰路徑，鑑別聲明伺服器配發，應為.der 檔。
    $rp_prikey_path = "/cert/idp_key.pem";					// 解密JWE 用的der 格式私鑰路徑，文件內含加密格式為RSA-OAEP-256 的私鑰以及自簽證書。RP 端自行產生
    $rpKeyLibPas = "zta_yourpassword";						// 解密JWE 用的der 格式私鑰的密碼
/*	---- { Mike Temp Del for test
	echo "jweToken = ".$jweToken.".<br>\n";
	echo "clientIp = ".$clientIp.".<br>\n";
	echo "userId = ".$userId.".<br>\n";
	echo "rpId = ".$rpId.".<br>\n";

	---- } */
	$result = $zta->verifyToken($jweToken, $identity_pubkey_path, $rp_prikey_path, $rpKeyLibPas, $clientIp, $userId, $rpId);
    $verificationResult = json_decode($result, true);           // convert JSON string to array
	
	// Create SAML response (using a library like SimpleSAMLphp is recommended)
    $samlResponse = createSAMLResponse($samlId, $reqIssuer, $destination, $assertionConsumerServiceURL, $verificationResult);
	$deflatedResponse = gzdeflate($samlResponse);
//	$encodedResponse = base64_encode($deflatedResponse);
    $encodedResponse = base64_encode($samlResponse);
    error_log("samlResponse: " . $samlResponse);
    error_log("encodedResponse: " . $encodedResponse);

/*
		// Load the base64 decoded XML for validation
		$decodedXml = base64_decode($encodedResponse);
		$doc2 = new DOMDocument();
		$doc2->loadXML($decodedXml);

		// Locate the Signature node for validation
		$xpath = new DOMXPath($doc2);
		$xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
		$signatureNode = $xpath->query('//ds:Signature')->item(0);

		if (!$signatureNode) {
			die('No signature found.');
		}

		// Create a new security object
		$objXMLSecDSig = new XMLSecurityDSig();
		$objXMLSecDSig->sigNode = $signatureNode;  // Set the signature node
		$objXMLSecDSig->canonicalizeSignedInfo();
		$objXMLSecDSig->idKeys = array('ID');

		// Locate the public key (certificate) in the KeyInfo node
		$key = $objXMLSecDSig->locateKey();
		$key->loadKey($publicCert, true, true);

		// Validate the signature
		$isValid = $objXMLSecDSig->verify($key);
		if ($isValid) {
			echo "\nSignature is valid after Base64 encoding and decoding.\n<br>";
			echo "<h3>encodedResponse</h3><br>$encodedResponse<br>";
		} else {
			echo "\nSignature validation failed after Base64 encoding and decoding.\n";
		}
*/

    // Redirect back to SAML SP with SAML response and JWE token with a 301 status code by POST
    $postData = [
		'SAMLResponse'	=> $encodedResponse,
//		'X-ZTA-AUTH-USER'	=> 'tester2',
    ];

	echo '<html>';
	echo 	'<body onload="document.forms[\'samlRedirect\'].submit()">';
    echo 	'<form id="samlRedirect" name="samlRedirect" method="POST" action="' . htmlspecialchars($assertionConsumerServiceURL) . '">';
    foreach ($postData as $key => $value) {
        echo	'<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
    }
    echo	'</form>';
    echo	'</body>';
    echo '</html>';
    // Redirect back to SAML SP with SAML response and JWE token with a 301 status code
	$redirectURL = $assertionConsumerServiceURL . '?SAMLResponse=' . $encodedResponse . '&authToken=' . urlencode($jweToken);
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $redirectURL");
/*
*/
    exit;
/*
*/
} else {
    http_response_code(400);
    echo "Invalid request.";
    exit;
}

/* ============================================================
    Description: Verify the JWE token using the Java library
    Input : $jweToken : 加密的JWE token，來源為閘道端header，X-ZTA-AUTH-TOKEN。
            $clientIp : 客戶端的ip 位址，來源為閘道端header，X-Real-IP。
            $userId   : 使用者名稱，來源為閘道端header，X-ZTA-AUTH-USER。
            $rpId     : RP 伺服器端的名稱，來源為閘道端header，X-ZTA-AUTH-RPID。
   ============================================================
function verifyToken($jweToken, $clientIp, $userId, $rpId) {
    // Using exec to call Java code
    $identity_pubkey_path = "../webapps/rp0001/jwspublic.der";  // 解密JWS 用的公鑰路徑，鑑別聲明伺服器配發，應為.der 檔。
    $rp_prikey_path = "../webapps/rp0001/jwe_private_key.der";  // 解密JWE 用的der 格式私鑰路徑，文件內含加密格式為RSA-OAEP-256 的私鑰以及自簽證書。RP 端自行產生
    $rpKeyLibPas = "zta_yourpassword";                          // 解密JWE 用的der 格式私鑰的密碼
    $command = "java -cp /path/to/your/jar cht.zta.ZTA verifyToken "+
                "'$jweToken' "+
                "'$identity_pubkey_path' "+
                "'$rp_prikey_path' "+
                "'$rpKeyLibPas' "+
                "'$clientIp' "+
                "'$userId' "+
                "'$rpId'";
    $output = shell_exec($command);
    return json_decode($output, true);
}
 */
/* ============================================================
    Description: Verify the JWE token using the Java library
    Input : $samlId : 唯一標識這個請求的 ID
            $reqIssuer : 指明請求的發起者，即 SP 的標識
            $destination : 
            $assertionConsumerServiceURL   : 指定 IdP 應該將 SAML 回應發送到的 URL
            $verificationResult : Auth JSON result
   ============================================================ */
function createSAMLResponse($samlId, $reqIssuer, $destination, $assertionConsumerServiceURL, $verificationResult) {
    // Create a new XML document for the SAML Response
/*
*/
	global $privateKeyFile, $publicCertFile;
	
    $xml = new DOMDocument('1.0', 'UTF-8');
    
    // 1. Create the root Response element
	$response = $xml->createElementNS('urn:oasis:names:tc:SAML:2.0:protocol', 'saml2p:Response');
    $response->setAttribute('xmlns:xs', 'http://www.w3.org/2001/XMLSchema');
    $response->setAttribute('Destination', $assertionConsumerServiceURL);
//	$response->setAttribute('ID', '_response12345');			// addReference will automatic generate 'Id' field
    $response->setAttribute('InResponseTo', $samlId);
    $response->setAttribute('IssueInstant', gmdate('Y-m-d\TH:i:s\Z'));
    $response->setAttribute('Version', '2.0');
	$xml->appendChild($response); // This ensures that documentElement is set
	
    // 2. Add Issuer to Response
    $responseIssuer = $xml->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml2:Issuer');
    $responseIssuer->setAttribute('Format', 'urn:oasis:names:tc:SAML:2.0:nameid-format:entity');
    $responseIssuer->nodeValue = 'http://192.168.174.131/samlTestEntityID';			//or using $issuer->append('http://192.168.174.131/samlTestEntityID');
    $response->appendChild($responseIssuer);
    
/*	// (3). Sign the Response
    $responseDSig = new XMLSecurityDSig();
    $responseDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
    
    $responseDSig->addReference(
        $response, 
        XMLSecurityDSig::SHA256, 
        ['http://www.w3.org/2000/09/xmldsig#enveloped-signature']
    );
    
    $responseKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type'=>'private']);
    $responseKey->loadKey(dirname(__FILE__).'/cert/idp_key.pem', true);                         // path/to/private_key.pem
    
    $responseDSig->sign($responseKey);
    $responseDSig->add509Cert(file_get_contents(dirname(__FILE__).'/cert/idp_cert.pem'), true); // path/to/public_cert.pem
    $responseDSig->appendSignature($response);
*/    
    // 3. Create the Status element
    if ($verificationResult['valid'] == 'true') {
        $authStatus = 'Success';
        $userId = $verificationResult['userid']??'';
        $rpId = $verificationResult['rpid']??'';
        $clientIp = $verificationResult['clientip']??'';
    }
    else
        $authStatus = 'AuthnFailed';
    $status = $xml->createElementNS('urn:oasis:names:tc:SAML:2.0:protocol', 'saml2p:Status');
    $statusCode = $xml->createElement('saml2p:StatusCode');
    $statusCode->setAttribute('Value', "urn:oasis:names:tc:SAML:2.0:status:$authStatus");
    $status->appendChild($statusCode);
    $response->appendChild($status);

    // 4. Create the Assertion element
    $assertion = $xml->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml2:Assertion');
//  $assertion->setAttribute('ID', '_assertion12345');			// addReference will automatic generate 'Id' field
    $assertion->setAttribute('IssueInstant', gmdate('Y-m-d\TH:i:s\Z'));
    $assertion->setAttribute('Version', '2.0');
    
    // 4.1 Add Issuer to Assertion
    $assertionIssuer = $xml->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml2:Issuer');
    $assertionIssuer->setAttribute('Format', 'urn:oasis:names:tc:SAML:2.0:nameid-format:entity');
    $assertionIssuer->append('http://192.168.174.131/samlTestEntityID');
//	$assertionIssuer = $xml->createElement('saml2:Issuer', 'http://192.168.174.131/samlTestEntityID');
    $assertion->appendChild($assertionIssuer);

    // 4.2 Add Subject to Assertion
    $subject = $xml->createElement('saml2:Subject');
    $nameID = $xml->createElement('saml2:NameID', 'user@example.com');
    $nameID->setAttribute('Format', 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress');
    $subject->appendChild($nameID);
    
    $subjectConfirmation = $xml->createElement('saml2:SubjectConfirmation');
    $subjectConfirmation->setAttribute('Method', 'urn:oasis:names:tc:SAML:2.0:cm:bearer');
    $subjectConfirmationData = $xml->createElement('saml2:SubjectConfirmationData');
    $subjectConfirmationData->setAttribute('InResponseTo', $samlId);
    $subjectConfirmationData->setAttribute('NotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', time() + 600)); // 10 minutes
    $subjectConfirmationData->setAttribute('Recipient', $assertionConsumerServiceURL);
    $subjectConfirmation->appendChild($subjectConfirmationData);
    $subject->appendChild($subjectConfirmation);
    
    $assertion->appendChild($subject);
    
    // 4.3 Add Conditions to Assertion
    $conditions = $xml->createElement('saml2:Conditions');
    $conditions->setAttribute('NotBefore', gmdate('Y-m-d\TH:i:s\Z', time() - 60)); // 1 minute before
    $conditions->setAttribute('NotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', time() + 600)); // 10 minutes
    
    $audienceRestriction = $xml->createElement('saml2:AudienceRestriction');
    $audience = $xml->createElement('saml2:Audience', $reqIssuer);
    $audienceRestriction->appendChild($audience);
    $conditions->appendChild($audienceRestriction);
    
    $assertion->appendChild($conditions);
    
    if ($authStatus == 'Success') {
        // 4.4 Add AuthnStatement to Assertion
        $authnStatement = $xml->createElement('saml2:AuthnStatement');
        $authnStatement->setAttribute('AuthnInstant', gmdate('Y-m-d\TH:i:s\Z'));
        $authnStatement->setAttribute('SessionIndex', $samlId);
        
        $authnContext = $xml->createElement('saml2:AuthnContext');
        $authnContextClassRef = $xml->createElement('saml2:AuthnContextClassRef', 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport');
        $authnContext->appendChild($authnContextClassRef);
        $authnStatement->appendChild($authnContext);
        
        $assertion->appendChild($authnStatement);
        
        // Create the AttributeStatement element
        $attributeStatement = $xml->createElement('saml2:AttributeStatement');

        // Create an Attribute element (rp id)
        $attribute = $xml->createElement('saml2:Attribute');
        $attribute->setAttribute('Name', 'RP ID');
        $attribute->setAttribute('NameFormat', 'urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified'); // OID for email
        // Add AttributeValue to the Attribute
        $attributeValue = $xml->createElement('saml2:AttributeValue', $rpId);
        $attributeValue->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $attributeValue->setAttribute('xsi:type', 'xs:string');
        $attribute->appendChild($attributeValue);
        // Append the Attribute to the AttributeStatement
        $attributeStatement->appendChild($attribute);

        // Create an Attribute element (user id)
        $attribute = $xml->createElement('saml2:Attribute');
        $attribute->setAttribute('Name', 'USER ID');
        $attribute->setAttribute('NameFormat', 'urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified'); // OID for email
        // Add AttributeValue to the Attribute
        $attributeValue = $xml->createElement('saml2:AttributeValue', $userId);
        $attributeValue->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $attributeValue->setAttribute('xsi:type', 'xs:string');
        $attribute->appendChild($attributeValue);
        // Append the Attribute to the AttributeStatement
        $attributeStatement->appendChild($attribute);

        // Create an Attribute element (client Ip )
        $attribute = $xml->createElement('saml2:Attribute');
        $attribute->setAttribute('Name', 'CLIENT IP');
        $attribute->setAttribute('NameFormat', 'urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified'); // OID for email
        // Add AttributeValue to the Attribute
        $attributeValue = $xml->createElement('saml2:AttributeValue', $clientIp);
        $attributeValue->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $attributeValue->setAttribute('xsi:type', 'xs:string');
        $attribute->appendChild($attributeValue);
        // Append the Attribute to the AttributeStatement
        $attributeStatement->appendChild($attribute);

        // Append the AttributeStatement to the Assertion
        $assertion->appendChild($attributeStatement);
    }

    // 4.5 Sign the Assertion
    $assertionElement = $response->getElementsByTagName('Assertion')->item(0);
	signElement($assertion);
/*
    $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type'=>'private']);
	$objKey->loadKey($privateKeyFile, true);                              // path/to/private_key.pem

	$assertionSignature  = new XMLSecurityDSig();
	$assertionSignature ->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
	$assertionSignature ->addReference(
        $assertion, 
        XMLSecurityDSig::SHA256, 
        ['http://www.w3.org/2000/09/xmldsig#enveloped-signature']
    );
    $assertionSignature ->sign($objKey);
	// Attach the public certificate to the Signature
//	$assertionSignature ->add509Cert(file_get_contents(dirname(__FILE__).'/cert/idp_cert.pem'), true);      // path/to/public_cert.pem
	$assertionSignature ->add509Cert(file_get_contents($publicCertFile), true);      // path/to/public_cert.pem

	// Append the Signature to the Assertion
	$assertionSignatureNode = $assertionSignature ->insertSignature($assertion, $assertionIssuer->nextSibling);
//	$assertion->insertBefore($assertionSignatureNode, $assertionIssuer->nextSibling);
//	$assertionSignature ->appendSignature($assertion);
 */
    // Append the signed Assertion to the Response
    $response->appendChild($assertion);

    // 5. Sign the Response
    $responseElement = $response->documentElement;
    signElement($response);
/*
    $responseDSig = new XMLSecurityDSig();
    $responseDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
    
    $responseDSig->addReference(
        $response, 
        XMLSecurityDSig::SHA256, 
        ['http://www.w3.org/2000/09/xmldsig#enveloped-signature']
    );
    
    $responseKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type'=>'private']);
    $responseKey->loadKey($privateKeyFile, true);                         // path/to/private_key.pem
    
    // Before signing
    error_log("XML before signing: " . $xml->saveXML());

    $responseDSig->sign($responseKey);
	$responseDSig ->add509Cert(file_get_contents($publicCertFile), true);      // path/to/public_cert.pem

	// Append Signature to Response after Issuer
//	echo "<h3>response XML:</h3>\n".$xml->saveXML(), "\n";
	$responseSignatureNode = $responseDSig->insertSignature($response, $responseIssuer->nextSibling);

    // After signing
    error_log("XML after signing: " . $xml->saveXML());
*/
    // Append the signed Response to the document and output
//	echo "<h3>privateKey:</h3>\n".file_get_contents($privateKeyFile), "\n";
//	echo "<h3>publicCert:</h3>\n".file_get_contents($publicCertFile), "\n";
//	$xml->appendChild($response);
//	echo "e. ".$xml->saveXML(), "\n";

    return ($xml->saveXML());

/*
	// Simplified SAML response creation, use a library like SimpleSAMLphp for full implementation
	$responseTemplate = "<saml2p:Response xmlns:saml2p='urn:oasis:names:tc:SAML:2.0:protocol'"
									   . " xmlns:xs='http://www.w3.org/2001/XMLSchema'"
									   . " Destination='$assertionConsumerServiceURL'"
									   . " ID='id13959387811864661481791510'"
									   . " InResponseTo='$samlId'"
									   . " IssueInstant='".gmdate('Y-m-d\TH:i:s\Z')."'"
									   . " Version='2.0'"
									   . ">"
						  . "<saml2:Issuer xmlns:saml2='urn:oasis:names:tc:SAML:2.0:assertion'"
										. " Format='urn:oasis:names:tc:SAML:2.0:nameid-format:entity'"
										. " >http://192.168.174.131/samlTestEntityID</saml2:Issuer>"
						  . "<ds:Signature xmlns:ds='http://www.w3.org/2000/09/xmldsig#'>"
							  . "<ds:SignedInfo>"
								  . "<ds:CanonicalizationMethod Algorithm='http://www.w3.org/2001/10/xml-exc-c14n#' />"
								  . "<ds:SignatureMethod Algorithm='http://www.w3.org/2001/04/xmldsig-more#rsa-sha256' />"
								  . "<ds:Reference URI='#id13959387811864661481791510'>"
									  . "<ds:Transforms>"
										  . "<ds:Transform Algorithm='http://www.w3.org/2000/09/xmldsig#enveloped-signature' />"
										  . "<ds:Transform Algorithm='http://www.w3.org/2001/10/xml-exc-c14n#'>"
											  . "<ec:InclusiveNamespaces xmlns:ec='http://www.w3.org/2001/10/xml-exc-c14n#'"
																	  . " PrefixList='xs'"
																	  . " />"
										  . "</ds:Transform>"
									  . "</ds:Transforms>"
									  . "<ds:DigestMethod Algorithm='http://www.w3.org/2001/04/xmlenc#sha256' />"
									  . "<ds:DigestValue>mB/efqO4GLRMnsZImHpe4r2nj/NABlxQYAZ2ps/ngMQ=</ds:DigestValue>"
								  . "</ds:Reference>"
							  . "</ds:SignedInfo>"
							  . "<ds:SignatureValue>q84qbLKPgrUUHLIr5Tx67ktBVNopHVbiNdl61gxwNuBoNTvOaOd4/gDcfHVHfRwwugqV7bgualXRKV/kb2t3WWw7hbjt2Nz3nmK1ZAlJPHiVnqINP1MuRDJ+4bKIz/01iTYa7YHMCdPIKXXdvL4rh759K/nZQqz0nd6VUb/Pt5YBjqHPxSauj+TchLfO3EaZJSdsfv+dtXeMsYdLXbRB/Q4bDV9bDdEafxPmY2f05OVeegAckosBWEAGVws724tdUouAerLZwHBwhicStr0PcP7foN+vp66dv3zQN9xB8Rw538lcjVf5rZfJI3Ssamzys9/1rV9IrfEApx2n82lkgw==</ds:SignatureValue>"
							  . "<ds:KeyInfo>"
								  . "<ds:X509Data>"
									  . "<ds:X509Certificate>MIID6zCCAtOgAwIBAgIUKulVFRWB/HBlSjEmbT2bU+vPufIwDQYJKoZIhvcNAQELBQAwgZ0xCzAJ"
					  . "BgNVBAYTAlRXMQ8wDQYDVQQIDAZUYWl3YW4xDzANBgNVBAcMBlRhaXBlaTEWMBQGA1UECgwNS2lu"
					  . "Z2hvbGQgSW5jLjEWMBQGA1UECwwNUiYgRGVwYXJ0bWVudDEoMCYGCSqGSIb3DQEJARYZbWlrZS5j"
					  . "aGl1QGtpbmdob2xkLmNvbS50dzESMBAGA1UEAwwJbG9jYWxob3N0MB4XDTI0MDcxMDAwNTIzMVoX"
					  . "DTI1MDcxMDAwNTIzMVowgZ0xCzAJBgNVBAYTAlRXMQ8wDQYDVQQIDAZUYWl3YW4xDzANBgNVBAcM"
					  . "BlRhaXBlaTEWMBQGA1UECgwNS2luZ2hvbGQgSW5jLjEWMBQGA1UECwwNUiYgRGVwYXJ0bWVudDEo"
					  . "MCYGCSqGSIb3DQEJARYZbWlrZS5jaGl1QGtpbmdob2xkLmNvbS50dzESMBAGA1UEAwwJbG9jYWxo"
					  . "b3N0MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAr+2dst8DDr5gnfc1/7Rs9gxfR5P0"
					  . "s54o9DS0pB8b+4nhaWj+Z8RXmvElVRQNhDJnqvXhaSTgTuZwz2cTOIzu0BGSlazuABNM4s9QA4mZ"
					  . "YcWelyX8lgzuAE8yVfn/I350MBH7v4Z6tpxZCsVRbwwSJUpk3vuxAJwOGMIlGGH16wBXZvkUuLI0"
					  . "7fEBA0AHhCyNesG89r4ftV0BzaQGzs6PSJYyZ3Qu0/xG485kB4MgEwqJR86W/HUnx4yiQkN2j78X"
					  . "VShpPYHAybD2MWeB4CCAH7M3VAfCWUygwYxvJo/Ea/b6O4ue2xA7joo+R9gmc3lApxisbzCd4jA4"
					  . "SOVb5nbcKQIDAQABoyEwHzAdBgNVHQ4EFgQUPt/TMJ5MakPRQ+4vh3PJGv/SCcQwDQYJKoZIhvcN"
					  . "AQELBQADggEBAG4AwDH7U8D32M4apwfT9WFbMsZ92MhHRGpeq1oCZhCEfN2sa+g0sKGUAGl6Wcv5"
					  . "bPl0CWbwFm60vWPwsEDPbzNd4bOjcCMHdyXX+ACBmLewMslon70IhPjmuOkPh7UhRn6HuDwOixfu"
					  . "lbCEFfuNKXHGLQAdfhdv4PzjLp6/tbqc56JYArctUor5GRZ8IdJEGgSeHM/uIoOlZuvc9ftKTnJ8"
					  . "z7Me31JXXfDoqRME7CHHP44HZQLPjIxVOOEjPxCmwy5eBgNIpngZVAB36qeydKhaT/nd3Rna4+VW"
					  . "5R2X2XybeNUODWKR8b/amzHWm03zR8fIgGivh4ssOsP/x+s/VnA=</ds:X509Certificate>"
								  . "</ds:X509Data>"
							  . "</ds:KeyInfo>"
						  . "</ds:Signature>"
						  . "<saml2p:Status>"
//						  . "<saml2p:Status xmlns:saml2p='urn:oasis:names:tc:SAML:2.0:protocol'>"
							  . "<saml2p:StatusCode Value='urn:oasis:names:tc:SAML:2.0:status:Success' />"
						  . "</saml2p:Status>"
						  . "<saml2:Assertion xmlns:saml2='urn:oasis:names:tc:SAML:2.0:assertion'"
										   . " xmlns:xs='http://www.w3.org/2001/XMLSchema'"
										   . " ID='id13959387813421441369907846'"
										   . " IssueInstant='".gmdate('Y-m-d\TH:i:s\Z')."'"
										   . " Version='2.0'"
										   . ">"
							  . "<saml2:Issuer xmlns:saml2='urn:oasis:names:tc:SAML:2.0:assertion'"
											. " Format='urn:oasis:names:tc:SAML:2.0:nameid-format:entity'"
											. ">http://192.168.174.131/samlTestEntityID</saml2:Issuer>"
							  . "<ds:Signature xmlns:ds='http://www.w3.org/2000/09/xmldsig#'>"
								  . "<ds:SignedInfo>"
									  . "<ds:CanonicalizationMethod Algorithm='http://www.w3.org/2001/10/xml-exc-c14n#' />"
									  . "<ds:SignatureMethod Algorithm='http://www.w3.org/2001/04/xmldsig-more#rsa-sha256' />"
									  . "<ds:Reference URI='#id13959387813421441369907846'>"
										  . "<ds:Transforms>"
											  . "<ds:Transform Algorithm='http://www.w3.org/2000/09/xmldsig#enveloped-signature' />"
											  . "<ds:Transform Algorithm='http://www.w3.org/2001/10/xml-exc-c14n#'>"
												  . "<ec:InclusiveNamespaces xmlns:ec='http://www.w3.org/2001/10/xml-exc-c14n#'"
																		  . " PrefixList='xs'"
																		  . "/>"
											  . "</ds:Transform>"
										  . "</ds:Transforms>"
										  . "<ds:DigestMethod Algorithm='http://www.w3.org/2001/04/xmlenc#sha256' />"
										  . "<ds:DigestValue>47DEQpj8HBSa+/TImW+5JCeuQeRkm5NMpJWZG3hSuFU=</ds:DigestValue>"
									  . "</ds:Reference>"
								  . "</ds:SignedInfo>"
								  . "<ds:SignatureValue>VgFC22TwdgfOjLQxc7UiVfaQLnHyIU4HjCdaODr5uzg6TEs8gYhKuGIAGtE5JSorY5RzLO59ncK7Zd7Ri/jqZNVCF8SwUPiHphWZu1/04vd9bm84ugV03sqwugw+TNinGg1QWcgjVqZAFM5yhIs65yGf0+izuEbS3bWSydprZJDsjXlDFqews6eQo4mAoTquZIEzeMiGNiEGja43O8JLutNadeqQ+8q+2k/GjUt9DeuCHkiZE49nHawwp26Y4XkiBNFiPdIbo0tR85clp3+f8xFYCfV1HjMWS3XIUSmjJqYPMs9oOrlpxNwjcUgmL1WnKs4A5yjnVhH3jaYl+1cn8Q==</ds:SignatureValue>"
								  . "<ds:KeyInfo>"
									  . "<ds:X509Data>"
										  . "<ds:X509Certificate>MIID6zCCAtOgAwIBAgIUKulVFRWB/HBlSjEmbT2bU+vPufIwDQYJKoZIhvcNAQELBQAwgZ0xCzAJ"
					  . "BgNVBAYTAlRXMQ8wDQYDVQQIDAZUYWl3YW4xDzANBgNVBAcMBlRhaXBlaTEWMBQGA1UECgwNS2lu"
					  . "Z2hvbGQgSW5jLjEWMBQGA1UECwwNUiYgRGVwYXJ0bWVudDEoMCYGCSqGSIb3DQEJARYZbWlrZS5j"
					  . "aGl1QGtpbmdob2xkLmNvbS50dzESMBAGA1UEAwwJbG9jYWxob3N0MB4XDTI0MDcxMDAwNTIzMVoX"
					  . "DTI1MDcxMDAwNTIzMVowgZ0xCzAJBgNVBAYTAlRXMQ8wDQYDVQQIDAZUYWl3YW4xDzANBgNVBAcM"
					  . "BlRhaXBlaTEWMBQGA1UECgwNS2luZ2hvbGQgSW5jLjEWMBQGA1UECwwNUiYgRGVwYXJ0bWVudDEo"
					  . "MCYGCSqGSIb3DQEJARYZbWlrZS5jaGl1QGtpbmdob2xkLmNvbS50dzESMBAGA1UEAwwJbG9jYWxo"
					  . "b3N0MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAr+2dst8DDr5gnfc1/7Rs9gxfR5P0"
					  . "s54o9DS0pB8b+4nhaWj+Z8RXmvElVRQNhDJnqvXhaSTgTuZwz2cTOIzu0BGSlazuABNM4s9QA4mZ"
					  . "YcWelyX8lgzuAE8yVfn/I350MBH7v4Z6tpxZCsVRbwwSJUpk3vuxAJwOGMIlGGH16wBXZvkUuLI0"
					  . "7fEBA0AHhCyNesG89r4ftV0BzaQGzs6PSJYyZ3Qu0/xG485kB4MgEwqJR86W/HUnx4yiQkN2j78X"
					  . "VShpPYHAybD2MWeB4CCAH7M3VAfCWUygwYxvJo/Ea/b6O4ue2xA7joo+R9gmc3lApxisbzCd4jA4"
					  . "SOVb5nbcKQIDAQABoyEwHzAdBgNVHQ4EFgQUPt/TMJ5MakPRQ+4vh3PJGv/SCcQwDQYJKoZIhvcN"
					  . "AQELBQADggEBAG4AwDH7U8D32M4apwfT9WFbMsZ92MhHRGpeq1oCZhCEfN2sa+g0sKGUAGl6Wcv5"
					  . "bPl0CWbwFm60vWPwsEDPbzNd4bOjcCMHdyXX+ACBmLewMslon70IhPjmuOkPh7UhRn6HuDwOixfu"
					  . "lbCEFfuNKXHGLQAdfhdv4PzjLp6/tbqc56JYArctUor5GRZ8IdJEGgSeHM/uIoOlZuvc9ftKTnJ8"
					  . "z7Me31JXXfDoqRME7CHHP44HZQLPjIxVOOEjPxCmwy5eBgNIpngZVAB36qeydKhaT/nd3Rna4+VW"
					  . "5R2X2XybeNUODWKR8b/amzHWm03zR8fIgGivh4ssOsP/x+s/VnA=</ds:X509Certificate>"
									  . "</ds:X509Data>"
								  . "</ds:KeyInfo>"
							  . "</ds:Signature>"
							  . "<saml2:Subject xmlns:saml2='urn:oasis:names:tc:SAML:2.0:assertion'>"
								  . "<saml2:NameID Format='urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified'>tester2</saml2:NameID>"
								  . "<saml2:SubjectConfirmation Method='urn:oasis:names:tc:SAML:2.0:cm:bearer'>"
									  . "<saml2:SubjectConfirmationData InResponseTo='$samlId'"
																	 . " NotOnOrAfter='".gmdate('Y-m-d\TH:i:s\Z', time() + 600)."'"
																	 . " Recipient='$assertionConsumerServiceURL'"
																	 . "/>"
								  . "</saml2:SubjectConfirmation>"
							  . "</saml2:Subject>"
							  . "<saml2:Conditions xmlns:saml2='urn:oasis:names:tc:SAML:2.0:assertion'"
												. " NotBefore='".gmdate('Y-m-d\TH:i:s\Z', time() - 60)."'"
												. " NotOnOrAfter='".gmdate('Y-m-d\TH:i:s\Z', time() + 600)."'"
												. ">"
								  . "<saml2:AudienceRestriction>"
									  . "<saml2:Audience>$reqIssuer</saml2:Audience>"
								  . "</saml2:AudienceRestriction>"
							  . "</saml2:Conditions>"
							  . "<saml2:AuthnStatement xmlns:saml2='urn:oasis:names:tc:SAML:2.0:assertion'"
													. " AuthnInstant='".gmdate('Y-m-d\TH:i:s\Z')."'"
													. " SessionIndex='$samlId'"
													. ">"
								  . "<saml2:AuthnContext>"
									  . "<saml2:AuthnContextClassRef>urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport</saml2:AuthnContextClassRef>"
								  . "</saml2:AuthnContext>"
							  . "</saml2:AuthnStatement>"
							  . "<saml2:AttributeStatement xmlns:saml2='urn:oasis:names:tc:SAML:2.0:assertion'>"
								  . "<saml2:Attribute Name='OS Account'"
												   . " NameFormat='urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified'"
												   . ">"
									  . "<saml2:AttributeValue xmlns:xs='http://www.w3.org/2001/XMLSchema'"
															. " xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'"
															. " xsi:type='xs:string'"
															. ">qa</saml2:AttributeValue>"
								  . "</saml2:Attribute>"
								  . "<saml2:Attribute Name='Firstname'"
												   . " NameFormat='urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified'"
												   . ">"
									  . "<saml2:AttributeValue xmlns:xs='http://www.w3.org/2001/XMLSchema'"
															. " xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'"
															. " xsi:type='xs:string'"
															. ">User1</saml2:AttributeValue>"
								  . "</saml2:Attribute>"
							  . "</saml2:AttributeStatement>"
						  . "</saml2:Assertion>"
					  . "</saml2p:Response>";
*/

    return ($responseTemplate);
}

function signElement(DOMElement $element) {
    global $privateKeyFile, $publicCertFile;

    $objDSig = new XMLSecurityDSig();
    $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
    $objDSig->addReference(
        $element,
        XMLSecurityDSig::SHA256,
        ['http://www.w3.org/2000/09/xmldsig#enveloped-signature', XMLSecurityDSig::EXC_C14N],
        ['force_uri' => true]
    );

    $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
    $objKey->loadKey($privateKeyFile, true);

    $objDSig->sign($objKey);
    $objDSig->add509Cert(file_get_contents($publicCertFile));

    // Insert the Signature after the Issuer element
    $issuer = $element->getElementsByTagName('Issuer')->item(0);
	$signatureNode = $objDSig ->insertSignature($element, $issuer->nextSibling);
/*
    if ($issuer) {
        if ($isAssertion) {
            $element->insertBefore($objDSig->sigNode, $issuer->nextSibling);
        } else {
            $statusNode = $element->getElementsByTagName('Status')->item(0);
            if ($statusNode) {
                $element->insertBefore($objDSig->sigNode, $statusNode);
            } else {
                $element->insertBefore($objDSig->sigNode, $issuer->nextSibling);
            }
        }
    } else
        $objDSig->appendSignature($element);
*/
}

function generateSAMLKeys($countryName, $stateOrProvinceName, $localityName, $organizationName, $commonName, $emailAddress) {
    // Generate private key
    $privateKey = openssl_pkey_new([
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ]);

    // Generate CSR
    $csr = openssl_csr_new([
        "countryName" => $countryName,
        "stateOrProvinceName" => $stateOrProvinceName,
        "localityName" => $localityName,
        "organizationName" => $organizationName,
        "commonName" => $commonName,
        "emailAddress" => $emailAddress
    ], $privateKey);

    // Generate self-signed certificate
    $x509 = openssl_csr_sign($csr, null, $privateKey, 365);

    // Export private key to PEM format
    openssl_pkey_export($privateKey, $privateKeyPEM);

    // Export certificate to PEM format
    openssl_x509_export($x509, $certificatePEM);

    return [
        'privateKey' => $privateKeyPEM,
        'certificate' => $certificatePEM
    ];
}

    /**
     * Add signature key and sender certificate to an element (Message or Assertion).
     *
     * @param \SimpleSAML\Configuration $srcMetadata The metadata of the sender.
     * @param \SimpleSAML\Configuration $dstMetadata The metadata of the recipient.
     * @param \SAML2\SignedElement $element The element we should add the data to.
     */
    function addSign(
        Configuration $srcMetadata,
        Configuration $dstMetadata,
        SignedElement $element,
    ): void {
        $dstPrivateKey = $dstMetadata->getOptionalString('signature.privatekey', null);
        $cryptoUtils = new Utils\Crypto();

        if ($dstPrivateKey !== null) {
            /** @var array $keyArray */
            $keyArray = $cryptoUtils->loadPrivateKey($dstMetadata, true, 'signature.');
            $certArray = $cryptoUtils->loadPublicKey($dstMetadata, false, 'signature.');
        } else {
            /** @var array $keyArray */
            $keyArray = $cryptoUtils->loadPrivateKey($srcMetadata, true);
            $certArray = $cryptoUtils->loadPublicKey($srcMetadata, false);
        }

        $algo = $dstMetadata->getOptionalString('signature.algorithm', null);
        if ($algo === null) {
            $algo = $srcMetadata->getOptionalString('signature.algorithm', XMLSecurityKey::RSA_SHA256);
        }

        $privateKey = new XMLSecurityKey($algo, ['type' => 'private']);
        if (array_key_exists('password', $keyArray)) {
            $privateKey->passphrase = $keyArray['password'];
        }
        $privateKey->loadKey($keyArray['PEM'], false);

        $element->setSignatureKey($privateKey);

        if ($certArray === null) {
            // we don't have a certificate to add
            return;
        }

        if (!array_key_exists('PEM', $certArray)) {
            // we have a public key with only a fingerprint
            return;
        }

        $element->setCertificates([$certArray['PEM']]);
    }

?>
