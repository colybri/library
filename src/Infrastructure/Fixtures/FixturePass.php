<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Fixtures;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FixturePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->has(FixtureRegistry::class)) {
            throw new \InvalidArgumentException(FixtureRegistry::class . ' has to be defined as a service');
        }

        $definition = $container->findDefinition(FixtureRegistry::class);
        $taggedServices = $container->findTaggedServiceIds('library.fixture');


        foreach (array_keys($taggedServices) as $serviceId) {
            $definition->addMethodCall('addFixture', [new Reference($serviceId)]);
        }
    }
}