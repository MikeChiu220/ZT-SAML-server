<?php

/**
 * SAML 2.0 remote SP metadata for SimpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-sp-remote
 */

/*
 * Example SimpleSAMLphp SAML 2.0 SP
 */
$metadata['https://saml2sp.example.org'] = [
    'AssertionConsumerService' => 'https://saml2.example.org/module.php/saml/sp/saml2-acs.php/default-sp',
    'SingleLogoutService' => 'https://saml2sp.example.org/module.php/saml/sp/saml2-logout.php/default-sp',
];

/*
 * This example shows an example config that works with Google Workspace (G Suite / Google Apps) for education.
 * What is important is that you have an attribute in your IdP that maps to the local part of the email address at
 * Google Workspace. In example, if your Google account is foo.com, and you have a user that has an email john@foo.com,
 * then you must properly configure the saml:AttributeNameID authproc-filter with the name of an attribute that for
 * this user has the value of 'john'.
 */
$metadata['google.com'] = [
    'AssertionConsumerService' => 'https://www.google.com/a/g.feide.no/acs',
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
    'authproc' => [
      1 => [
        'class' => 'saml:AttributeNameID',
        'identifyingAttribute' => 'uid',
        'Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
      ],
    ],
    'simplesaml.attributes' => false,
];

$metadata['https://legacy.example.edu'] = [
    'AssertionConsumerService' => 'https://legacy.example.edu/saml/acs',
    /*
     * Currently, SimpleSAMLphp defaults to the SHA-256 hashing algorithm.
     * Uncomment the following option to use SHA-1 for signatures directed
     * at this specific service provider if it does not support SHA-256 yet.
     *
     * WARNING: SHA-1 is disallowed starting January the 1st, 2014.
     * Please refer to the following document for more information:
     * http://csrc.nist.gov/publications/nistpubs/800-131A/sp800-131A.pdf
     */
    //'signature.algorithm' => 'http://www.w3.org/2000/09/xmldsig#rsa-sha1',
];

/*
 * SAML 2.0 SP for Zentera	Mike[2024/09/09]
 */
$metadata['https://192.168.10.60/launcher'] = [
	'AssertionConsumerService' => [
		[
			'index' => 1,
			'isDefault' => true,
			'Location' => 'https://192.168.10.60/launcher/access/procSamlResp',
			'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
		],
	],
	'SingleLogoutService' => [
    [
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
      'Location' => 'https://192.168.10.60/launcher/access/procLogout',
    ],
  ],
	'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
	// Certificate used to verify the signature from the IdP
	// Public certificate in PEM format (base64 encoded)
	'x509cert' => 'MIID6zCCAtOgAwIBAgIUKulVFRWB/HBlSjEmbT2bU+vPufIwDQYJKoZIhvcNAQEL
BQAwgZ0xCzAJBgNVBAYTAlRXMQ8wDQYDVQQIDAZUYWl3YW4xDzANBgNVBAcMBlRh
aXBlaTEWMBQGA1UECgwNS2luZ2hvbGQgSW5jLjEWMBQGA1UECwwNUiYgRGVwYXJ0
bWVudDEoMCYGCSqGSIb3DQEJARYZbWlrZS5jaGl1QGtpbmdob2xkLmNvbS50dzES
MBAGA1UEAwwJbG9jYWxob3N0MB4XDTI0MDcxMDAwNTIzMVoXDTI1MDcxMDAwNTIz
MVowgZ0xCzAJBgNVBAYTAlRXMQ8wDQYDVQQIDAZUYWl3YW4xDzANBgNVBAcMBlRh
aXBlaTEWMBQGA1UECgwNS2luZ2hvbGQgSW5jLjEWMBQGA1UECwwNUiYgRGVwYXJ0
bWVudDEoMCYGCSqGSIb3DQEJARYZbWlrZS5jaGl1QGtpbmdob2xkLmNvbS50dzES
MBAGA1UEAwwJbG9jYWxob3N0MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKC
AQEAr+2dst8DDr5gnfc1/7Rs9gxfR5P0s54o9DS0pB8b+4nhaWj+Z8RXmvElVRQN
hDJnqvXhaSTgTuZwz2cTOIzu0BGSlazuABNM4s9QA4mZYcWelyX8lgzuAE8yVfn/
I350MBH7v4Z6tpxZCsVRbwwSJUpk3vuxAJwOGMIlGGH16wBXZvkUuLI07fEBA0AH
hCyNesG89r4ftV0BzaQGzs6PSJYyZ3Qu0/xG485kB4MgEwqJR86W/HUnx4yiQkN2
j78XVShpPYHAybD2MWeB4CCAH7M3VAfCWUygwYxvJo/Ea/b6O4ue2xA7joo+R9gm
c3lApxisbzCd4jA4SOVb5nbcKQIDAQABoyEwHzAdBgNVHQ4EFgQUPt/TMJ5MakPR
Q+4vh3PJGv/SCcQwDQYJKoZIhvcNAQELBQADggEBAG4AwDH7U8D32M4apwfT9WFb
MsZ92MhHRGpeq1oCZhCEfN2sa+g0sKGUAGl6Wcv5bPl0CWbwFm60vWPwsEDPbzNd
4bOjcCMHdyXX+ACBmLewMslon70IhPjmuOkPh7UhRn6HuDwOixfulbCEFfuNKXHG
LQAdfhdv4PzjLp6/tbqc56JYArctUor5GRZ8IdJEGgSeHM/uIoOlZuvc9ftKTnJ8
z7Me31JXXfDoqRME7CHHP44HZQLPjIxVOOEjPxCmwy5eBgNIpngZVAB36qeydKha
T/nd3Rna4+VW5R2X2XybeNUODWKR8b/amzHWm03zR8fIgGivh4ssOsP/x+s/VnA=',
	// Optional: If the IdP requires signing requests, add a private key for signing requests
	'privatekey' => 'server.key',
	'certificate' => 'server.crt',

];

$metadata['https://apacrd2.zentera.net/launcher'] = [
	'AssertionConsumerService' => [
		[
			'index' => 1,
			'isDefault' => true,
			'Location' => 'https://apacrd2.zentera.net/launcher/access/procSamlResp',
			'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
		],
	],
	'SingleLogoutService' => [
    [
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
      'Location' => 'https://apacrd2.zentera.net/launcher/access/procLogout',
    ],
  ],
	'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
	// Certificate used to verify the signature from the IdP
	// Public certificate in PEM format (base64 encoded)
	'x509cert' => 'MIID0DCCArgCFEyHZdILXln7FCC2NlEYEK+yNzDCMA0GCSqGSIb3DQEBCwUAMIGsMRgwFgYDVQQDDA9raW5naG9sZC5jb20udHcxCzAJBgNVBAYTAlRXMQ8wDQYDVQQIDAZUQUlXQU4xEzARBgNVBAcMCk5FVyBUQUlQRUkxHDAaBgNVBAoME0tpbmdob2xkIFRlY2hub2xvZ3kxFjAUBgNVBAsMDVJEIERlcGFydG1lbnQxJzAlBgkqhkiG9w0BCQEWGGFkbWluLmtoQGtpbmdob2xkLmNvbS50dzAeFw0yNDA3MTEwMjM2NTlaFw0yNTA3MTEwMjM2NTlaMIGbMQswCQYDVQQGEwJUVzEPMA0GA1UECAwGVEFJV0FOMRMwEQYDVQQHDApORVcgVEFJUEVJMRwwGgYDVQQKDBNLaW5naG9sZCBUZWNobm9sb2d5MRYwFAYDVQQLDA1SRCBEZXBhcnRtZW50MRgwFgYDVQQDDA9raW5naG9sZC5jb20udHcxFjAUBgoJkiaJk/IsZAEBDAZTRVJWRVIwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDemZeHxKGp68N4IIYUSh2+WK8EDplKcEvYxXcfWgOdaZMiSpXym5m08ua/Op99Jh2fsOdZ/Hv9iQb37DIvp1yFafLmbU4I16fIxa6jak+uRvdnvJcd8i5lXiUB7h7XYFiOsObGQsPMXu0GeVbpU22w2ndX2KYjGtQV3xEXA/gYk/CJIz5SpEEi1FUgapDsD6ZWsa2iUDV7yzu9Zk4Oelior7W6wTyy1nvAekUy+447ZqgCj1vVawTN0ameUQDxf83LsBCZdQvprWRolfqxnkxoDlFjPF8NfrWYj/UKvmFDZcIfzbroVp9twDg+NsozoFJugE97WEuvaxmddXHzMPtPAgMBAAEwDQYJKoZIhvcNAQELBQADggEBAPh/MfPxlLy+SdssacDHyuZ9WjJAJXeuEIE3m6MMCZK1NhhYTn18zvW1yOlRYDHF2jezyembEILXXH1pKFKGB6YG4vasW1YwCh4ogpcKJdxWfINTP/re9M0SYeOi6dWMHNPOE82p2SknuAh9hYkxdB7jOaZoTJFnjTUvJ3fI8YREOwqTTaa31k6Ih1VB5ZvHn6x7SQF94QYgZCDeSaBKfkT8zYFiiF3yPjqv2jDUZXLq9W1BYNVyQSOsrA9ktShofV/8haOSnr+2CjFJj6UOLwG6F1PwhZ5aOCKI98yehFBvtOeevGblJ+H+omfsWnHzqKswsX8FE5I07sCsmLjlIxk=',
	// Optional: If the IdP requires signing requests, add a private key for signing requests
	'privatekey' => 'idp_key.pem',
	'certificate' => 'idp_cert.pem',

];
