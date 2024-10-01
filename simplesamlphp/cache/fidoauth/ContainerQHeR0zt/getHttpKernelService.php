<?php

namespace ContainerQHeR0zt;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/*
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getHttpKernelService extends SimpleSAML_KernelProdContainer
{
    /*
     * Gets the public 'http_kernel' shared service.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernel
     */
    public static function do($container, $lazyLoad = true)
    {
        $a = new \Symfony\Component\EventDispatcher\EventDispatcher();
        $a->addSubscriber(($container->privates['router_listener'] ?? self::getRouterListenerService($container)));
        $a->addSubscriber(new \Symfony\Component\HttpKernel\EventListener\ResponseListener('UTF-8'));
        $b = new \Symfony\Component\HttpKernel\Controller\ContainerControllerResolver($container);
        $b->allowControllers(['SimpleSAML\\Kernel']);

        return $container->services['http_kernel'] = new \Symfony\Component\HttpKernel\HttpKernel($a, $b, ($container->services['request_stack'] ??= new \Symfony\Component\HttpFoundation\RequestStack()), new \Symfony\Component\HttpKernel\Controller\ArgumentResolver(new \Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory(), new RewindableGenerator(function () use ($container) {
            yield 0 => ($container->privates['argument_resolver.backed_enum_resolver'] ??= new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\BackedEnumValueResolver());
            yield 1 => ($container->privates['argument_resolver.uid'] ??= new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\UidValueResolver());
            yield 2 => ($container->privates['argument_resolver.datetime'] ??= new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\DateTimeValueResolver(NULL));
            yield 3 => ($container->privates['argument_resolver.request_attribute'] ??= new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver());
            yield 4 => ($container->privates['argument_resolver.request'] ??= new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver());
            yield 5 => ($container->privates['argument_resolver.session'] ??= new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\SessionValueResolver());
            yield 6 => ($container->privates['argument_resolver.service'] ?? $container->load('getArgumentResolver_ServiceService'));
            yield 7 => ($container->privates['argument_resolver.default'] ??= new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\DefaultValueResolver());
            yield 8 => ($container->privates['argument_resolver.variadic'] ??= new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\VariadicValueResolver());
        }, 9), new \Symfony\Component\DependencyInjection\Argument\ServiceLocator($container->getService ??= $container->getService(...), [
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\BackedEnumValueResolver' => ['privates', 'argument_resolver.backed_enum_resolver', 'getArgumentResolver_BackedEnumResolverService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\DateTimeValueResolver' => ['privates', 'argument_resolver.datetime', 'getArgumentResolver_DatetimeService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\DefaultValueResolver' => ['privates', 'argument_resolver.default', 'getArgumentResolver_DefaultService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\QueryParameterValueResolver' => ['privates', 'argument_resolver.query_parameter_value_resolver', 'getArgumentResolver_QueryParameterValueResolverService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestAttributeValueResolver' => ['privates', 'argument_resolver.request_attribute', 'getArgumentResolver_RequestAttributeService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestPayloadValueResolver' => ['privates', 'argument_resolver.request_payload', NULL, 'You can neither use "#[MapRequestPayload]" nor "#[MapQueryString]" since the Serializer component is not installed. Try running "composer require symfony/serializer-pack".'],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestValueResolver' => ['privates', 'argument_resolver.request', 'getArgumentResolver_RequestService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\ServiceValueResolver' => ['privates', 'argument_resolver.service', 'getArgumentResolver_ServiceService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\SessionValueResolver' => ['privates', 'argument_resolver.session', 'getArgumentResolver_SessionService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\UidValueResolver' => ['privates', 'argument_resolver.uid', 'getArgumentResolver_UidService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\VariadicValueResolver' => ['privates', 'argument_resolver.variadic', 'getArgumentResolver_VariadicService', true],
        ], [
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\BackedEnumValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\BackedEnumValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\DateTimeValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\DateTimeValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\DefaultValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\DefaultValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\QueryParameterValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\QueryParameterValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestAttributeValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestAttributeValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestPayloadValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestPayloadValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\ServiceValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\ServiceValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\SessionValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\SessionValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\UidValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\UidValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\VariadicValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\VariadicValueResolver',
        ])));
    }
}
