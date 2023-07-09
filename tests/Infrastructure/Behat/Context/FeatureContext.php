<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Infrastructure\Behat\Context;

use Behat\Behat\Context\Context;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;

final class FeatureContext implements Context
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /** @Given the environment clean */
    public function cleanEnvironment(): void
    {
        $this->bootstrapEnvironment();
    }

    /** @Given the environment with fixtures */
    public function loadFixtures(): void
    {
        $this->bootstrapEnvironment();

        $application = $this->getApplication();

        $arg = new \Symfony\Component\Console\Input\ArrayInput(
            [
                'command' => 'library:fixtures:generate',
                '--no-interaction' => true,
            ],
        );

        $application->run($arg, new \Symfony\Component\Console\Output\NullOutput());
    }


    private function bootstrapEnvironment(): void
    {
        $application = $this->getApplication();

        $arg = new \Symfony\Component\Console\Input\ArrayInput(
            [
                'command' => 'library:fixtures:clean',
                '--no-interaction' => true,
            ],
        );

        $application->run($arg, new \Symfony\Component\Console\Output\NullOutput());

    }

    private function getApplication(): Application
    {
        $app = new Application($this->kernel);
        $app->setAutoExit(false);

        return $app;
    }
}
