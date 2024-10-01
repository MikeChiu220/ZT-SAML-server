<?php

/**
 * SAML 2.0 IdP configuration for SimpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-hosted
 */

// $metadata['urn:x-simplesamlphp:example-idp'] = [
// $metadata['__DYNAMIC:1__'] = [
// $metadata['http://192.168.174.131/samlTestEntityID'] = [
$metadata['https://miketestubuntuvm/samlTestEntityID'] = [
        /*
     * The hostname of the server (VHOST) that will use this SAML entity.
     *
     * Can be '__DEFAULT__', to use this entry by default.
     */
    'host' => '__DEFAULT__',

    // X.509 key and certificate. Relative to the cert directory.
	'privatekey' => 'server.key',
	'certificate' => 'server.crt',
    'sign.response' => TRUE,            // 啟用回應簽署
    'sign.assertion' => TRUE,           // 啟用斷言簽署
    'signature.algorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
    'attributes.NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:basic',

    /*
     * Authentication source to use. Must be one that is configured in
     * 'config/authsources.php'.
     */
    'auth' => 'fido-auth',
//    'auth' => 'example-userpass',

    /* Uncomment the following to use the uri NameFormat on attributes. */
    /*
    'attributes.NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
    'authproc' => [
        // Convert LDAP names to oids.
        100 => ['class' => 'core:AttributeMap', 'name2oid'],
    ],
    */

    /*
     * Uncomment the following to specify the registration information in the
     * exported metadata. Refer to:
     * http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/cs01/saml-metadata-rpi-v1.0-cs01.html
     * for more information.
     */
    /*
    'RegistrationInfo' => [
        'authority' => 'urn:mace:example.org',
        'instant' => '2008-01-17T11:28:03Z',
        'policies' => [
            'en' => 'http://example.org/policy',
            'es' => 'http://example.org/politica',
        ],
    ],
    */
    'SingleLogoutService' => array(
        array(
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'https://miketestubuntuvm/simplesaml/saml2/idp/SingleLogoutService.php',
        ),
    ),
];
