<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Smoke;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouterInterface;

final class KernelBootTest extends KernelTestCase
{
    /**
     * @test
     */
    public function kernel_boots_and_builds_the_buses(): void
    {
        $kernel = self::bootKernel();

        self::assertSame('test', $kernel->getEnvironment());
        self::assertTrue(self::getContainer()->has('command.bus'));
        self::assertTrue(self::getContainer()->has('query.bus'));
    }

    /**
     * @test
     */
    public function every_api_route_points_to_an_existing_controller(): void
    {
        self::bootKernel();

        $routes = self::getContainer()->get(RouterInterface::class)->getRouteCollection();
        $apiRoutes = 0;

        foreach ($routes as $name => $route) {
            if (!str_starts_with($route->getPath(), '/v1/')) {
                continue;
            }

            ++$apiRoutes;
            $controller = $route->getDefault('_controller');

            self::assertIsString($controller, sprintf('Route "%s" has no controller', $name));
            self::assertTrue(class_exists($controller), sprintf('Route "%s" points to missing controller "%s"', $name, $controller));
            self::assertNotEmpty($route->getMethods(), sprintf('Route "%s" accepts any HTTP method', $name));
        }

        self::assertSame(18, $apiRoutes);
    }
}
