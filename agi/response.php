<?php

require_once 'vendor/autoload.php'; // Assuming you are using Composer for xmlseclibs

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

$samlId		= '_ded8a97a-7847-45af-83ec-dc1c7756561a';
$reqIssuer	= 'https://192.168.0.120/launcher';
$destination= '';
$assertionConsumerServiceURL= 'https://192.168.0.120/launcher/access/procSamlResp';
$verificationResult['valid'] = 'true';

    $xml = new DOMDocument('1.0', 'UTF-8');
    
    // Create the root Response element
    $response = $xml->createElementNS('urn:oasis:names:tc:SAML:2.0:protocol', 'samlp:Response');
    $response->setAttribute('Destination', $assertionConsumerServiceURL);
//	$response->setAttribute('ID', '_response12345');			// addReference will automatic generate 'Id' field
    $response->setAttribute('InResponseTo', $samlId);
    $response->setAttribute('IssueInstant', gmdate('Y-m-d\TH:i:s\Z'));
    $response->setAttribute('Version', '2.0');
    $xml->appendChild($response); // This ensures that documentElement is set

    // Add Issuer to Response
    $issuer = $xml->createElement('saml:Issuer', 'https://idp.example.com');
    $response->appendChild($issuer);
    
    // Sign the Response
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
    
    // Create the Status element
    if ($verificationResult['valid'] == 'true')
        $authStatus = 'Success';
    else
        $authStatus = 'AuthnFailed';
    $status = $xml->createElement('samlp:Status');
    $statusCode = $xml->createElement('samlp:StatusCode');
    $statusCode->setAttribute('Value', "urn:oasis:names:tc:SAML:2.0:status:$authStatus");
    $status->appendChild($statusCode);
    $response->appendChild($status);

    // Create the Assertion element
    $assertion = $xml->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:Assertion');
//	$assertion->setAttribute('ID', '_assertion12345');			// addReference will automatic generate 'Id' field
    $assertion->setAttribute('IssueInstant', gmdate('Y-m-d\TH:i:s\Z'));
    $assertion->setAttribute('Version', '2.0');
    
    // Add Issuer to Assertion
    $assertionIssuer = $xml->createElement('saml:Issuer', 'https://idp.example.com');
    $assertion->appendChild($assertionIssuer);
    
    // Add Subject to Assertion
    $subject = $xml->createElement('saml:Subject');
    $nameID = $xml->createElement('saml:NameID', 'user@example.com');
    $nameID->setAttribute('Format', 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress');
    $subject->appendChild($nameID);
    
    $subjectConfirmation = $xml->createElement('saml:SubjectConfirmation');
    $subjectConfirmation->setAttribute('Method', 'urn:oasis:names:tc:SAML:2.0:cm:bearer');
    $subjectConfirmationData = $xml->createElement('saml:SubjectConfirmationData');
    $subjectConfirmationData->setAttribute('InResponseTo', $samlId);
    $subjectConfirmationData->setAttribute('NotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', time() + 600)); // 10 minutes
    $subjectConfirmationData->setAttribute('Recipient', $assertionConsumerServiceURL);
    $subjectConfirmation->appendChild($subjectConfirmationData);
    $subject->appendChild($subjectConfirmation);
    
    $assertion->appendChild($subject);
    
    // Add Conditions to Assertion
    $conditions = $xml->createElement('saml:Conditions');
    $conditions->setAttribute('NotBefore', gmdate('Y-m-d\TH:i:s\Z', time() - 60)); // 1 minute before
    $conditions->setAttribute('NotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', time() + 600)); // 10 minutes
    
    $audienceRestriction = $xml->createElement('saml:AudienceRestriction');
    $audience = $xml->createElement('saml:Audience', $reqIssuer);
    $audienceRestriction->appendChild($audience);
    $conditions->appendChild($audienceRestriction);
    
    $assertion->appendChild($conditions);
    
    // Add AuthnStatement to Assertion
    $authnStatement = $xml->createElement('saml:AuthnStatement');
    $authnStatement->setAttribute('AuthnInstant', gmdate('Y-m-d\TH:i:s\Z'));
    $authnStatement->setAttribute('SessionIndex', $samlId);
    
    $authnContext = $xml->createElement('saml:AuthnContext');
    $authnContextClassRef = $xml->createElement('saml:AuthnContextClassRef', 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport');
    $authnContext->appendChild($authnContextClassRef);
    $authnStatement->appendChild($authnContext);
    
    $assertion->appendChild($authnStatement);
    
	// Create the AttributeStatement element
	$attributeStatement = $xml->createElement('saml:AttributeStatement');

	// Create an Attribute element (example: user email)
	$attribute = $xml->createElement('saml:Attribute');
	$attribute->setAttribute('Name', 'urn:oid:0.9.2342.19200300.100.1.3'); // OID for email

	// Add AttributeValue to the Attribute
	$attributeValue = $xml->createElement('saml:AttributeValue', 'user@example.com');
	$attributeValue->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
	$attributeValue->setAttribute('xsi:type', 'xs:string');
	$attribute->appendChild($attributeValue);

	// Append the Attribute to the AttributeStatement
	$attributeStatement->appendChild($attribute);

	// Append the AttributeStatement to the Assertion
	$assertion->appendChild($attributeStatement);

    // Sign the Assertion
    $objDSig = new XMLSecurityDSig();
    $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
    
    $objDSig->addReference(
        $assertion, 
        XMLSecurityDSig::SHA256, 
        ['http://www.w3.org/2000/09/xmldsig#enveloped-signature']
    );
    
    $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type'=>'private']);
    $objKey->loadKey(dirname(__FILE__).'/cert/idp_key.pem', true);                              // path/to/private_key.pem
    
    $objDSig->sign($objKey);
    $objDSig->add509Cert(file_get_contents(dirname(__FILE__).'/cert/idp_cert.pem'), true);      // path/to/public_cert.pem
    $objDSig->appendSignature($assertion);
    
    // Append the signed Assertion to the Response
    $response->appendChild($assertion);
    
    // Append the signed Response to the document and output
    $xml->appendChild($response);
    echo $xml->saveXML();
    
?>