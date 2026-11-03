<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Smoke;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Keeps docs/openapi in step with the routes: every route is documented and
 * every documented operation has a route.
 */
final class OpenApiSpecTest extends KernelTestCase
{
    private const SPEC_DIR = __DIR__ . '/../../docs/openapi';
    private const HTTP_METHODS = ['get', 'put', 'post', 'delete', 'patch', 'head', 'options'];

    #[Test]
    public function every_route_is_documented_and_every_documented_operation_has_a_route(): void
    {
        self::bootKernel();

        $routes = [];
        foreach (self::getContainer()->get(RouterInterface::class)->getRouteCollection() as $route) {
            if (!str_starts_with($route->getPath(), '/api/v1/')) {
                continue;
            }
            foreach ($route->getMethods() ?: ['POST'] as $method) {
                $routes[] = strtoupper($method) . ' ' . $route->getPath();
            }
        }

        $documented = $this->documentedOperations();

        sort($routes);
        sort($documented);

        self::assertSame($routes, $documented);
    }

    /** @return list<string> "METHOD /path" of every operation in openapi.yml */
    private function documentedOperations(): array
    {
        $spec = Yaml::parseFile(self::SPEC_DIR . '/openapi.yml');
        $operations = [];

        foreach ($spec['paths'] as $path => $item) {
            if (isset($item['$ref'])) {
                [$file, $pointer] = explode('#/', $item['$ref'], 2);
                $item = Yaml::parseFile(self::SPEC_DIR . '/' . $file)[$pointer];
            }
            foreach (array_intersect(array_keys($item), self::HTTP_METHODS) as $method) {
                $operations[] = strtoupper($method) . ' ' . $path;
            }
        }

        return $operations;
    }
}
