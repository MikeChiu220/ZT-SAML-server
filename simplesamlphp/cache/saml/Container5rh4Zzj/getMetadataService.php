<?php

namespace Container5rh4Zzj;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/*
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getMetadataService extends SimpleSAML_KernelProdContainer
{
    /*
     * Gets the public 'SimpleSAML\Module\saml\Controller\Metadata' shared autowired service.
     *
     * @return \SimpleSAML\Module\saml\Controller\Metadata
     */
    public static function do($container, $lazyLoad = true)
    {
        return $container->services['SimpleSAML\\Module\\saml\\Controller\\Metadata'] = new \SimpleSAML\Module\saml\Controller\Metadata(($container->privates['SimpleSAML\\Configuration'] ?? $container->load('getConfigurationService')));
    }
}
