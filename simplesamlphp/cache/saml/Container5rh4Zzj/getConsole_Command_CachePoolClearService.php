<?php

namespace Container5rh4Zzj;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/*
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getConsole_Command_CachePoolClearService extends SimpleSAML_KernelProdContainer
{
    /*
     * Gets the private 'console.command.cache_pool_clear' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Command\CachePoolClearCommand
     */
    public static function do($container, $lazyLoad = true)
    {
        $container->privates['console.command.cache_pool_clear'] = $instance = new \Symfony\Bundle\FrameworkBundle\Command\CachePoolClearCommand(($container->services['cache.global_clearer'] ?? $container->load('getCache_GlobalClearerService')), ['cache.app', 'cache.system', 'cache.validator', 'cache.serializer', 'cache.annotations', 'cache.property_info']);

        $instance->setName('cache:pool:clear');
        $instance->setDescription('Clear cache pools');

        return $instance;
    }
}
