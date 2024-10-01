<?php
require_once('../vendor/autoload.php');
SimpleSAML\Configuration::setConfigDir('../config');

$authState = $_REQUEST['AuthState'] ?? null;
$as = new SimpleSAML\Auth\Simple('example-userpass');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        if ($authState !== null) {
            $state = SimpleSAML\Auth\State::loadState($authState, 'example-userpass:login');
            $source = SimpleSAML\Auth\Source::getById($state['example-userpass:AuthID']);
            if ($source === null) {
                throw new Exception('Authentication source not found.');
            }
            $source->login($username, $password);
        } else {
            // Direct login without SAML flow
            try {
                $as->login([
                    'saml:AuthnContextClassRef' => 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport',
                    'UserName' => $username,
                    'Password' => $password,
                ]);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        // If login is successful, redirect to the IdP script or a default page
        header('Location: ' . ($authState ? SimpleSAML\Module::getModuleURL('core/loginuserpass.php') : 'index.php'));
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Show login form
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form method="post">
        <?php if ($authState !== null): ?>
            <input type="hidden" name="AuthState" value="<?php echo htmlspecialchars($authState); ?>" />
        <?php endif; ?>
        Username: <input type="text" name="username"><br>
        Password: <input type="password" name="password"><br>
        <input type="submit" value="Login">
    </form>
    <?php
    if (isset($error)) {
        echo "<p>Error: " . htmlspecialchars($error) . "</p>";
    }
    ?>
</body>
</html>