<?php
// login.php
include("include/connection.php");//Mysql連結

// Function to parse SAML request (you can use libraries like SimpleSAMLphp for more complex parsing)
function parseSAMLRequest($samlRequest) {
    // Decode from BASE64
    $decodedRequest = base64_decode($samlRequest);
    // Inflate (decompress) the request
    $inflatedRequest = gzinflate($decodedRequest);
    // Parse the XML
    $xml = new SimpleXMLElement($inflatedRequest);

    // Debug output: Show the entire XML structure
/*
	echo '<pre>', htmlentities($inflatedRequest), '</pre><br>';
	print_r($_SERVER);
    echo "<h2>Raw XML Object:</h2>";
    echo "<pre>";
    print_r($xml);
    echo "</pre><br>";
*/
    $xml->registerXPathNamespace('samlp', 'urn:oasis:names:tc:SAML:2.0:protocol');
    $xml->registerXPathNamespace('saml', 'urn:oasis:names:tc:SAML:2.0:assertion');

    // Extract attributes using the correct namespaces
    $samlId = (string)$xml->attributes()->ID;
    $destination = (string)$xml->attributes()->Destination;
    $assertionConsumerServiceURL = (string)$xml->attributes()->AssertionConsumerServiceURL;
    // Extract the Issuer element using XPath
    $issuerNodes = $xml->xpath('//saml:Issuer');
    $Issuer = isset($issuerNodes[0]) ? trim((string)$issuerNodes[0]) : 'Not found';

	/* Debug output: Show extracted values
    echo "<h2>Issuer Object:</h2>";
    echo "<pre>";
    print_r($issuerNodes);
    echo "</pre><br>";
    echo "<h2>Extracted Values:</h2>";
    echo "ID: " . ($samlId ? $samlId : "Not found") . "<br>";
    echo "Issuer: " . ($Issuer ? $Issuer : "Not found") . "<br>";
    echo "Destination: " . ($destination ? $destination : "Not found") . "<br>";
    echo "AssertionConsumerServiceURL: " . ($assertionConsumerServiceURL ? $assertionConsumerServiceURL : "Not found") . "<br>";
*/
    return [
        'samlId' => $samlId,
        'Issuer' => $Issuer,
        'destination' => $destination,
        'assertionConsumerServiceURL' => $assertionConsumerServiceURL
    ];
}
$fidoServerLoginURL = "https://MikeTestUbuntuVM/simplesaml/saml2/idp/SSOService.php?".$_SERVER['QUERY_STRING'];;
$pswServerLoginURL = "https://MikeTestUbuntuVM/simplesaml/saml_idp.php?".$_SERVER['QUERY_STRING'];;
header("HTTP/1.1 301 Moved Permanently");
	header("Location: $pswServerLoginURL");
	exit;
/*
*/

$samlRequest = $_GET['SAMLRequest']??"";
$gatewayIp = $_SERVER['REMOTE_ADDR']??"";               // PQC Gateway IP address
$clientIp = $_SERVER['HTTP_X_CLIENT_IP']??"";           // Device IP Address
/*
echo "samlRequest = $samlRequest<br>";
echo "REMOTE_ADDR = $gatewayIp<br>";
echo "HTTP_X_CLIENT_IP = $clientIp<br>";
*/
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $samlRequest && $gatewayIp) {           // && $cilentIP
    $parsedRequest = parseSAMLRequest($samlRequest);

    $Issuer = $parsedRequest['Issuer'];                                             // 這個欄位用來指明請求的發起者，即 SP 的標識
    $assertionConsumerServiceURL = $parsedRequest['assertionConsumerServiceURL'];   // 指定 IdP 應該將 SAML 回應發送到的 URL
    $samlId = $parsedRequest['samlId'];                                                 // 唯一標識這個請求的 ID
/*
	echo "samlId = $samlId<br>";
    echo "issuer = $Issuer<br>";
    echo "assertionConsumerServiceURL = $assertionConsumerServiceURL<br>";
    echo "gatewayIp = $gatewayIp<br>";
    echo "clientIp = $clientIp<br>";
  $NameIDPolicy = $parsedRequest['NameIDPolicy'];                                 // 指定 SP 希望 IdP 返回的 NameID 格式
    $RequestedAuthnContext = $parsedRequest['RequestedAuthnContext'];               // 指定 SP 所要求的認證上下文類型，通常是要求的安全等級
*/
    $destination = $parsedRequest['destination'];

    // Store SAML details in session or database as needed
    session_start();
    $_SESSION['samlId'] = $samlId;
    $_SESSION['reqIssuer'] = $Issuer;
    $_SESSION['destination'] = $destination;
    $_SESSION['assertionConsumerServiceURL'] = $assertionConsumerServiceURL;
    $_SESSION['gatewayIp'] = $gatewayIp;
    $_SESSION['clientIp'] = $clientIp;
	$command = "select samlId from loginSession WHERE samlId='$samlId'";
	$qryResult = FUN_SQL_QUERY($command, $database);
	$total_record = FUN_SQL_NUM_ROWS($qryResult);	//$ 總筆數
	if ( $total_record )
		$command = "UPDATE loginSession SET destination='$destination'," .
											" assertionConsumerServiceURL='$assertionConsumerServiceURL'," .
											" gatewayIp='$gatewayIp'," .
											" clientIp='$clientIp'," .
											" userid='$userid'," .
											" reqIssuer='$Issuer'" .
											" WHERE samlId='$samlId'";
	else
		$command = "INSERT INTO loginSession SET destination='$destination'," .
											" assertionConsumerServiceURL='$assertionConsumerServiceURL'," .
											" gatewayIp='$gatewayIp'," .
											" clientIp='$clientIp'," .
											" userid='$userid'," .
											" reqIssuer='$Issuer'," .
											" samlId='$samlId'";
//	echo $command;
	$qryResult = FUN_SQL_QUERY($command, $database);

    // Redirect to FIDO server with a 301 status code
/*	---- { Mike Temp for test
	$fidoServerLoginURL = "https://ag2.xtrust.hinet.net/fidologin";
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: $fidoServerLoginURL");
*/
	$fidoServerLoginURL = "http://192.168.174.131/agi/authResult";
//	header("HTTP/1.1 301 Moved Permanently");
//	header("Location: $fidoServerLoginURL");
    // Redirect to FIDO server using 301 with POST and custom header
    $postData = [
        'X-ZTA-AUTH-TOKEN'	=> 'test token',
        'X-Real-IP' 		=> '192.168.174.1',
        'X-ZTA-AUTH-USER'	=> 'tester2',
        'X-ZTA-AUTH-RPID'	=> 'VoIP',
    ];

    echo '<html>';
    echo '<body onload="document.forms[\'fidoRedirect\'].submit()">';
    echo '<form id="fidoRedirect" name="fidoRedirect" method="POST" action="' . htmlspecialchars($fidoServerLoginURL) . '">';
    foreach ($postData as $key => $value) {
        echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
    }
    echo '</form>';
    echo '</body>';
    echo '</html>';
/*
*/
	exit;
//	---- }
}
else {
    http_response_code(400);
    if ( strlen($smalRequest) == 0 )
        echo "SAMLRequest missing($smalRequest).<br>";
    if ( $gatewayIp === "" )
        echo "REMOTE_ADDR field missing($gatewayIp).<br>";
    if ( $cilentIP === "" )
        echo "HTTP_X_CLIENT_IP field missing($cilentIP).<br>";
	exit;
}
?>
