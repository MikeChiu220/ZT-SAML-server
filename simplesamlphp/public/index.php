<?php

declare(strict_types=1);

namespace SimpleSAML;

require_once('_include.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (session_id() == '') {
    echo "Session not started!";
} else {
    echo "Session ID: " . session_id();
    var_dump(session_id());
    var_dump($_COOKIE);
}

$config = Configuration::getInstance();
$httpUtils = new Utils\HTTP();

$headers = $config->getOptionalArray('headers.security', Configuration::DEFAULT_SECURITY_HEADERS);
$redirect = $config->getOptionalString('frontpage.redirect', Module::getModuleURL('core/welcome'));
$response =  new HTTP\RunnableResponse([$httpUtils, 'redirectTrustedURL'], [$redirect]);
foreach ($headers as $header => $value) {
    // Some pages may have specific requirements that we must follow. Don't touch them.
    if (!$response->headers->has($header)) {
        $response->headers->set($header, $value);
    }
}
$response->send();
