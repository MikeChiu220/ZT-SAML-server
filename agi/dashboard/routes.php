<?php
require_once 'controllers/StatusController.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/SysEventsController.php';
require_once 'middleware/JwtMiddleware.php';

function route($uri, $method) {
    $routes = [
        '/api/v1/status' => ['GET' => 'StatusController::getStatus'],
        '/api/v1/auth/login' => ['POST' => 'AuthController::login'],
        '/api/v1/sys-events' => ['GET' => 'SysEventsController::getEvents'],
    ];

    if (isset($routes[$uri][$method])) {
        if ($uri !== '/api/v1/auth/login') {
            JwtMiddleware::authenticate();
        }

        $handler = explode('::', $routes[$uri][$method]);
        $controller = new $handler[0]();
        $method = $handler[1];
        $controller->$method();
    } else {
        header("HTTP/1.0 404 Not Found");
        echo json_encode(['error' => 'Not Found']);
    }
}