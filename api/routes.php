<?php
require_once 'controllers/config.php'; // Ensure you have your database config included
require_once 'controllers/StatusController.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/SysEventsController.php';
require_once 'controllers/UploadPqcGatewayKey.php';
require_once 'middleware/JwtMiddleware.php';

function route($uri, $method) {
    // Parse the URL to get the path without query parameters
    $parsedUrl = parse_url($uri);
    $path = $parsedUrl['path']; // Get the path part of the URL
    $routes = [
        '/api/v1/status' => ['GET' => 'StatusController::getStatus'],
        '/api/v1/auth/login' => ['POST' => 'AuthController::login'],
        '/api/v1/sys-events' => ['GET' => 'SysEventsController::getEvents'],
        '/api/v1/satellite' => ['GET' => 'SysEventsController::getSatInfo'],
        '/api/v1/getSignAuth' => ['POST' => 'UploadPqcGatewayKey::getSignAuth'],
        '/upload' => ['POST' => 'UploadPqcGatewayKey::uploadPublicKey'],
    ];

    if (isset($routes[$path][$method])) {
        if ($uri !== '/api/v1/auth/login') {
//            JwtMiddleware::authenticate();        // Temp disable login check
        }

        $handler = explode('::', $routes[$path][$method]);
        $controller = new $handler[0]();
        $method = $handler[1];
        $controller->$method();
    } else {
        header("HTTP/1.0 404 Not Found");
        echo json_encode(['error' => 'Not Found']);
    }
}