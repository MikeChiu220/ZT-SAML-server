<?php
// logout.php

$fidoServerLogoutURL = "https://MikeTestUbuntuVM/simplesaml/logout.php?".$_SERVER['QUERY_STRING'];;
header("HTTP/1.1 301 Moved Permanently");
	header("Location: $fidoServerLogoutURL");
	exit;

// Function to parse SAML LogoutRequest (you can use libraries like SimpleSAMLphp for more complex parsing)
function parseSAMLLogoutRequest($samlRequest) {
    // Decoding and parsing logic here
    $decodedRequest = base64_decode($samlRequest);
    $xml = new SimpleXMLElement($decodedRequest);

    $logoutRequestId = (string)$xml['ID'];
    $issuer = (string)$xml->Issuer;
    $nameID = (string)$xml->NameID;
    $sessionIndex = (string)$xml->SessionIndex;

    return [
        'logoutRequestId' => $logoutRequestId,
        'issuer' => $issuer,
        'nameID' => $nameID,
        'sessionIndex' => $sessionIndex
    ];
}

function createSAMLLogoutResponse($logoutRequestId, $destination) {
    $responseTemplate = '<samlp:LogoutResponse xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="%s" InResponseTo="%s" Version="2.0" IssueInstant="%s" Destination="%s"><samlp:Status><samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/></samlp:Status></samlp:LogoutResponse>';
    $responseId = 'response_' . bin2hex(random_bytes(16));
    return sprintf($responseTemplate, $responseId, $logoutRequestId, gmdate('Y-m-d\TH:i:s\Z'), $destination);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['SAMLRequest'])) {
    $samlRequest = $_POST['SAMLRequest'];
    $parsedRequest = parseSAMLLogoutRequest($samlRequest);

    $logoutRequestId = $parsedRequest['logoutRequestId'];
    $issuer = $parsedRequest['issuer'];
    $nameID = $parsedRequest['nameID'];
    $sessionIndex = $parsedRequest['sessionIndex'];

    // Handle the logout process here (e.g., terminating the user session)
    session_start();
    session_destroy();

    // Create a SAML LogoutResponse
    $destination = $issuer; // Typically, the destination would be the Issuer
    $samlLogoutResponse = createSAMLLogoutResponse($logoutRequestId, $destination);

    // Redirect back to the SP with the LogoutResponse
    $redirectURL = $destination . '?SAMLResponse=' . base64_encode($samlLogoutResponse);
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $redirectURL");
    exit;
} else {
    http_response_code(400);
    echo "Invalid request.";
    exit;
}
?>
