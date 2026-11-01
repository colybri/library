<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Smoke;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;

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
            // The login check is answered by the JWT firewall, not by a controller.
            if ($name === 'api_login_check' || !str_starts_with($route->getPath(), '/api/v1/')) {
                continue;
            }

            ++$apiRoutes;
            $controller = $route->getDefault('_controller');

            self::assertIsString($controller, sprintf('Route "%s" has no controller', $name));
            self::assertTrue(class_exists($controller), sprintf('Route "%s" points to missing controller "%s"', $name, $controller));
            self::assertNotEmpty($route->getMethods(), sprintf('Route "%s" accepts any HTTP method', $name));
        }

        // A route name reused in two files silently replaces the first route.
        self::assertSame($this->declaredApiRoutes(), $apiRoutes);
    }

    private function declaredApiRoutes(): int
    {
        $count = 0;
        foreach (glob(dirname(__DIR__, 2) . '/config/routes/prod/*/routes.yaml') as $file) {
            $count += count(Yaml::parseFile($file));
        }

        return $count;
    }
}
