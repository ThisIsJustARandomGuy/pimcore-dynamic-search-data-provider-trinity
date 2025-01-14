<?php

namespace DsTrinityDataBundle\DependencyInjection\Compiler;

use DsTrinityDataBundle\Registry\ProxyResolverRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ProxyResolverPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('ds_trinity_data.proxy_resolver', true) as $id => $tags) {
            $definition = $container->getDefinition(ProxyResolverRegistry::class);
            foreach ($tags as $attributes) {
                $definition->addMethodCall('register', [new Reference($id), $attributes['identifier'], $attributes['type']]);
            }
        }
    }
}
