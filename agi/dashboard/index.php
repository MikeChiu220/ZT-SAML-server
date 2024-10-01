<?php
require_once 'config.php';
require_once 'routes.php';
require_once 'JwtAuth.php';

// Parse the request
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Route the request
route($request_uri, $request_method);