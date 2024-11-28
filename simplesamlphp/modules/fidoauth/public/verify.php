<?php

$path= dirname(__FILE__).'/../../../lib/_autoload.php';
require_once(dirname(__FILE__).'/../../../lib/_autoload.php');

use SimpleSAML\Auth\State;
use SimpleSAML\Module\fidoauth\Auth\Source\Fido;
use SimpleSAML\Error\BadRequest;

// Receive JSON data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if (!isset($data['AuthState']) || !isset($data['fidoResponse'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

try {
    $state = State::loadState($data['AuthState'], 'fidoauth:AuthState');
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid AuthState']);
    exit;
}

$sourceId = $state['\SimpleSAML\Auth\Source.id'] ?? null;

if ($sourceId === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing AuthSource in state']);
    exit;
}

try {
    $source = \SimpleSAML\Auth\Source::getById($sourceId);
    if (!($source instanceof Fido)) {
        throw new BadRequest('Invalid AuthSource type');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid AuthSource: ' . $e->getMessage()]);
    exit;
}

try {
    // Validate the FIDO response
    $validationResult = $source->validateAuthenticationResponse($state, $data['fidoResponse']);

    if ($validationResult) {
        // Authentication successful
        State::deleteState($state);
        echo json_encode(['success' => true]);
    } else {
        // Authentication failed
        echo json_encode(['success' => false, 'error' => 'FIDO authentication failed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error during validation: ' . $e->getMessage()]);
}