<?php

namespace Colybri\Library;

use Colybri\Library\Infrastructure\Fixtures\FixturePass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FixturePass());
    }

    private function configureContainer(ContainerConfigurator $container): void
    {
        $configDir = $this->getConfigDir();

        $container->import($configDir . '/{packages}/*.yaml');
        $container->import($configDir . '/{packages}/' . $this->environment . '/*.yaml');
        $container->import($configDir . '/services.yaml');
        // Loaded after services.yaml on purpose: imports declared inside
        // services.yaml are parsed before its own definitions, so its
        // "Colybri\Library\" resource would replace every explicit definition
        // in these files.
        $container->import($configDir . '/context/*.yaml');
        $container->import($configDir . '/{services}_' . $this->environment . '.yaml');
    }
}
