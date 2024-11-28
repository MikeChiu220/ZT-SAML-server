<?php

namespace SimpleSAML\Module\fidoauth\Auth\Source;

use SimpleSAML\Auth\Source;
use SimpleSAML\Auth\State;
use SimpleSAML\Error\Exception;
use SimpleSAML\Module;
use SimpleSAML\Utils\HTTP;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\TokenBinding\TokenBindingHandler;
use Cose\Algorithm\Manager as CoseAlgorithmManager;
use Cose\Algorithm\Signature\ECDSA;
use Cose\Algorithm\Signature\EdDSA;
use Cose\Algorithm\Signature\RSA;
use SAML2\AuthnRequest;
use SAML2\Response;
use SAML2\Assertion;
use SAML2\XML\saml\Issuer;
use SAML2\XML\saml\NameID;
use SAML2\Constants;
use SAML2\XML\ds\Signature;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\Module\fidoauth\CredentialSourceRepository;

class Fido extends Source
{
    private $rpEntity;
    private $userEntity;
    private $credentialSourceRepository;
    private $publicKeyCredentialLoader;
    private $assertionResponseValidator;

    public function __construct($info, $config)
    {
        parent::__construct($info, $config);

        $this->rpEntity = new PublicKeyCredentialRpEntity(
            'FIDO2 Service',
            'https://miketestubuntuvm/samlTestEntityID'
        );

        $this->credentialSourceRepository = new CredentialSourceRepository();
        // Create AttestationStatementSupportManager
        $attestationStatementSupportManager = new AttestationStatementSupportManager();
        $attestationStatementSupportManager->add(new NoneAttestationStatementSupport());
        // Add other attestation statement supports as needed

        // Create AttestationObjectLoader
        $attestationObjectLoader = new AttestationObjectLoader($attestationStatementSupportManager);

        // Initialize PublicKeyCredentialLoader with the AttestationObjectLoader
        $this->publicKeyCredentialLoader = new PublicKeyCredentialLoader($attestationObjectLoader);

        // Create TokenBindingHandler (or null if not needed)
        $tokenBindingHandler = null; // You can implement this if needed

        // Create ExtensionOutputCheckerHandler (or null if not needed)
        $extensionOutputCheckerHandler = null; // You can implement this if needed

        // Create CoseAlgorithmManager
        $coseAlgorithmManager = new CoseAlgorithmManager();
        $coseAlgorithmManager->add(new ECDSA\ES256());
        $coseAlgorithmManager->add(new ECDSA\ES384());
        $coseAlgorithmManager->add(new ECDSA\ES512());
        $coseAlgorithmManager->add(new EdDSA\Ed25519());
        $coseAlgorithmManager->add(new RSA\RS256());
        // Add other algorithms as needed

        $this->assertionResponseValidator = new AuthenticatorAssertionResponseValidator(
            $this->credentialSourceRepository,
            $tokenBindingHandler,
            $extensionOutputCheckerHandler,
            $coseAlgorithmManager
        );
    }

    public function authenticate(array &$state): void
    {
        $stateId = \SimpleSAML\Auth\State::saveState($state, 'fidoauth:AuthState');
        $state['fidoauth:AuthID'] = $stateId;

        // Create a user entity (you might want to get this from your user database)
        $this->userEntity = new PublicKeyCredentialUserEntity(
            $state['Attributes']['uid'][0] ?? '',
            $state['Attributes']['uid'][0] ?? '',
            $state['Attributes']['displayName'][0] ?? 'Unknown User'
        );

        // Generate getRequestOptions For authentication
        $requestOptions = $this->getRequestOptions();
        // Save the options in the state for later verification
        $state['fidoauth:requestOptions'] = $requestOptions;

        // Redirect to the FIDO2 login page
        $url = Module::getModuleURL('fidoauth/login1.php');
        // Ensure the URL is HTTPS
        $url = str_replace('http://', 'https://', $url);
        error_log("Redirecting to: " . $url);  // Add this line for debug
        $httpUtils = new HTTP();
        $httpUtils->redirectTrustedURL($url, array('AuthState' => $stateId));
    }

    private function getRequestOptions(): PublicKeyCredentialRequestOptions
    {
/*      
        return PublicKeyCredentialRequestOptions::create(
            \random_bytes(32),
            $this->rpEntity->getId()
        );
*/
        $challenge = random_bytes(32);
        
        $options = PublicKeyCredentialRequestOptions::create(
            $challenge,
            $this->rpEntity->getId()
        );

        // You can customize the options further if needed
        // $options->setUserVerification(PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED);
        // $options->setTimeout(60000); // 60 seconds

        return $options;
    }

    public function verifyAuthentication(array &$state): void
    {
        $publicKeyCredential = $this->publicKeyCredentialLoader->load($state['fidoauth:credential']);

        $authenticatorAssertionResponse = $publicKeyCredential->getResponse();
        if (!$authenticatorAssertionResponse instanceof AuthenticatorAssertionResponse) {
            throw new Exception('Invalid response type');
        }

        $request = $state['fidoauth:requestOptions'];

        try {
            $this->assertionResponseValidator->check(
                $publicKeyCredential->getRawId(),
                $authenticatorAssertionResponse,
                $request,
                $this->rpEntity->getId(),
                [$this->userEntity->getId()]
            );
        } catch (\Throwable $exception) {
            throw new Exception('Authentication failed: ' . $exception->getMessage());
        }

        // If we get here, authentication was successful
        $this->sendSAMLResponse($state);
    }

    public function getAuthenticationChallenge(): PublicKeyCredentialRequestOptions
    {
        $challenge = random_bytes(32);
        
        $options = PublicKeyCredentialRequestOptions::create(
            $challenge,
            $this->rpEntity->getId()
        );

        // You can customize the options further if needed
        // $options->setUserVerification(PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED);
        // $options->setTimeout(60000); // 60 seconds

        return $options;
    }

    private function sendSAMLResponse(array &$state): void
    {
        if (!isset($state['saml:sp:IdP']) || !isset($state['saml:sp:AuthnRequest'])) {
            throw new Exception('Missing SAML data in state');
        }

        $idp = $state['saml:sp:IdP'];
        $spEntityId = $state['saml:sp:EntityId'];
        $request = $state['saml:sp:AuthnRequest'];

        $now = time();
        $responseId = \SimpleSAML\Utils\Random::generateID();

        $response = new Response();
        $response->setIssuer(new Issuer($idp));
        $response->setDestination($request->getAssertionConsumerServiceURL());
        $response->setIssueInstant($now);
        $response->setID($responseId);
        $response->setInResponseTo($request->getId());

        $assertion = new Assertion();
        $assertion->setId(\SimpleSAML\Utils\Random::generateID());
        $assertion->setIssueInstant($now);
        $assertion->setIssuer(new Issuer($idp));
        $assertion->setNotBefore($now - 30);
        $assertion->setNotOnOrAfter($now + 300); // 5 minutes expiration

        $nameId = new NameID();
        $nameId->setValue($this->userEntity->getId());
        $nameId->setFormat(Constants::NAMEID_UNSPECIFIED);
        $assertion->setNameId($nameId);

        $assertion->setValidAudiences([$spEntityId]);

        $authnContext = [
            'AuthnContextClassRef' => [Constants::AC_PASSWORD_PROTECTED_TRANSPORT],
        ];
        $assertion->setAuthnContext($authnContext);

        $attributes = [
            'uid' => [$this->userEntity->getId()],
            'displayName' => [$this->userEntity->getDisplayName()],
        ];
        $assertion->setAttributes($attributes);

        // Sign the assertion
        $this->signXML($assertion);

        // Add the signed assertion to the response
        $response->setAssertions([$assertion]);

        // Sign the response
        $this->signXML($response);

        // Send the response
        $binding = new \SAML2\HTTPPost();
        $binding->send($response);
    }

    private function signXML($element): void
    {
        $privateKeyPath = $this->getPrivateKeyPath();
        $certificatePath = $this->getCertificatePath();

        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $objKey->loadKey($privateKeyPath, true);

        $element->setSignatureKey($objKey);
        $element->setCertificates([file_get_contents($certificatePath)]);
        
        // This will add the Signature element in the correct position
        $element->setSignature(new Signature());
    }

    private function getPrivateKeyPath(): string
    {
        // Implement this method to return the path to your private key file
        return '/var/www/html/simplesamlphp/cert/server.key';
    }

    private function getCertificatePath(): string
    {
        // Implement this method to return the path to your certificate file
        return '/var/www/html/simplesamlphp/cert/server.crt';
    }
}