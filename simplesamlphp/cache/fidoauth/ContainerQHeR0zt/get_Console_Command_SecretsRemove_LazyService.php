<?php

namespace ContainerQHeR0zt;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/*
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class get_Console_Command_SecretsRemove_LazyService extends SimpleSAML_KernelProdContainer
{
    /*
     * Gets the private '.console.command.secrets_remove.lazy' shared service.
     *
     * @return \Symfony\Component\Console\Command\LazyCommand
     */
    public static function do($container, $lazyLoad = true)
    {
        return $container->privates['.console.command.secrets_remove.lazy'] = new \Symfony\Component\Console\Command\LazyCommand('secrets:remove', [], 'Remove a secret from the vault', false, #[\Closure(name: 'console.command.secrets_remove', class: 'Symfony\\Bundle\\FrameworkBundle\\Command\\SecretsRemoveCommand')] fn (): \Symfony\Bundle\FrameworkBundle\Command\SecretsRemoveCommand => ($container->privates['console.command.secrets_remove'] ?? $container->load('getConsole_Command_SecretsRemoveService')));
    }
}
