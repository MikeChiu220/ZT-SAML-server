<?php

namespace ContainerXKhbRj9;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/*
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getLoginService extends SimpleSAML_KernelProdContainer
{
    /*
     * Gets the public 'SimpleSAML\Module\core\Controller\Login' shared autowired service.
     *
     * @return \SimpleSAML\Module\core\Controller\Login
     */
    public static function do($container, $lazyLoad = true)
    {
        return $container->services['SimpleSAML\\Module\\core\\Controller\\Login'] = new \SimpleSAML\Module\core\Controller\Login(($container->privates['SimpleSAML\\Configuration'] ?? $container->load('getConfigurationService')));
    }
}
