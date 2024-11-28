<?php

$authStateId = $_REQUEST['AuthState'];
$state = \SimpleSAML\Auth\State::loadState($authStateId, 'fidoauth:AuthID');
$source = \SimpleSAML\Auth\Source::getById($state['fidoauth:AuthID']);

if (!$source instanceof \SimpleSAML\Module\fidoauth\Auth\Source\Fido) {
    throw new \SimpleSAML\Error\Exception('Invalid authentication source: ' . var_export($state['fidoauth:AuthID'], true));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle FIDO authentication response
    try {
        $source->verifyAuthentication($state);
        \SimpleSAML\Auth\Source::completeAuth($state);
    } catch (\Exception $e) {
        $error = $e->getMessage();
    }
} else {
    // Display FIDO authentication challenge
    $challenge = $source->getAuthenticationChallenge();
}

$t = new \SimpleSAML\XHTML\Template($config, 'fidoauth:login.tpl.php');
$t->data['AuthState'] = $authStateId;
$t->data['challenge'] = $challenge ?? null;
$t->data['error'] = $error ?? null;
$t->show();