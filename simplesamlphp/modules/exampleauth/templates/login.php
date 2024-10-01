<?php 

require_once(dirname(__FILE__, 4) . '/src/_autoload.php');

use SimpleSAML\Utils\HTTP;
$http = new HTTP(); 
$procUrl=$http->getSelfURL();
$procUrl=htmlspecialchars($http->getSelfURL());
SimpleSAML\Logger::debug('Session state at login.php: ' . var_export($state, true));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <form method="post" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <input type="submit" value="Login">
    </form>
</body>
</html>
