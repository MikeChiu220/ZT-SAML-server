<?php


require_once 'vendor/autoload.php'; // Assuming you are using Composer for xmlseclibs

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

$xml = new DOMDocument('1.0', 'UTF-8');

$response = $xml->createElement('Response');
$response->setAttribute('ID', '_response12345');
$response->setAttribute('Version', '2.0');
$response->setAttribute('IssueInstant', gmdate('Y-m-d\TH:i:s\Z'));
$response->setAttribute('Destination', 'https://sp.example.com/acs');

$assertion = $xml->createElement('Assertion');
$assertion->setAttribute('ID', '_assertion12345');
$assertion->setAttribute('IssueInstant', gmdate('Y-m-d\TH:i:s\Z'));
$assertion->setAttribute('Version', '2.0');

$response->appendChild($assertion);

// Sign the Assertion without namespaces
$objDSig = new XMLSecurityDSig();
$objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
$objDSig->addReference($assertion, XMLSecurityDSig::SHA256, ['http://www.w3.org/2000/09/xmldsig#enveloped-signature']);

$objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type'=>'private']);
$objKey->loadKey('path/to/private_key.pem', true);

$objDSig->sign($objKey);
$objDSig->add509Cert(file_get_contents('path/to/public_cert.pem'), true);
$objDSig->appendSignature($assertion);

$xml->appendChild($response);
echo $xml->saveXML();
?>