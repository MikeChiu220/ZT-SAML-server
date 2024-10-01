<?php

namespace SimpleSAML\Module\exampleauth\Auth\Source;

use SimpleSAML\Assert\Assert;
use SimpleSAML\Auth;
use SimpleSAML\Error;
use SimpleSAML\Module;
use SimpleSAML\Utils;
use SimpleSAML\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;

class MikeUserPass extends Auth\Source {
    public function authenticate(&$state): void {
        Logger::debug('MikeUserPass authenticate() called with state: ' . var_export($state, true));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            // Simple password check, replace with real authentication logic
            if ($username === 'user' && $password === 'password') {
                $attributes = [
                    'uid' => [$username],
                    'email' => [$username . '@example.com'],
                ];

                $state['Attributes'] = $attributes;

                // Complete the authentication process
                Source::completeAuth($state);
            } else {
                throw new Error('WRONGUSERPASS');
            }
        } else {
            // Redirect to login page if not POST
            Logger::debug('Session state before redirect: ' . var_export($state, true));
            $httpUtils = new Utils\HTTP();
/*          $loginUrl = Module::getModuleURL('core/loginuserpass');
            $id = Auth\State::saveState($state, 'core:cardinality');
            $params = ['AuthState' => $id];
            $params = [];
            $httpUtils->redirectTrustedURL($loginUrl, $params);
*/
            $httpUtils->redirectTrustedURL('http://192.168.174.131/simplesamlphp/modules/exampleauth/templates/login.php');
            SimpleSAML\Logger::debug('Session state after redirect: ' . var_export($state, true));
        }
    }
}
