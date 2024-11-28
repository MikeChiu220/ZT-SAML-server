<?php

$path= dirname(__FILE__).'/../../../lib/_autoload.php';
require_once(dirname(__FILE__).'/../../../lib/_autoload.php');

use SimpleSAML\Auth\State;

$authStateId = $_REQUEST['AuthState'] ?? null;

if ($authStateId === null) {
    throw new SimpleSAML\Error\BadRequest('Missing AuthState parameter.');
}

$state = \SimpleSAML\Auth\State::loadState($authStateId, 'fidoauth:AuthState');

if (!isset($state['fidoauth:authenticated']) || $state['fidoauth:authenticated'] !== true) {
    throw new SimpleSAML\Error\BadRequest('Authentication was not completed.');
}
else
    SimpleSAML\Auth\Source::loginCompleted($state);
// Authentication successful, proceed with your application logic here
echo "Authentication successful!";