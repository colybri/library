<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Smoke;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityTest extends KernelTestCase
{
    public static function protectedRoutes(): iterable
    {
        yield 'match authors' => ['GET', '/api/v1/author/match?keywords=borges'];
        yield 'get country' => ['GET', '/api/v1/country/a28cf9dc-4a77-466a-b3c5-2987f50e2d28'];
        yield 'create book' => ['POST', '/api/v1/book'];
        yield 'delete edition' => ['DELETE', '/api/v1/edition/a28cf9dc-4a77-466a-b3c5-2987f50e2d28'];
    }

    #[Test]
    #[DataProvider('protectedRoutes')]
    public function given_no_jwt_when_an_api_route_is_requested_then_responds_unauthorized(string $method, string $uri): void
    {
        $kernel = self::bootKernel();

        $response = $kernel->handle(Request::create($uri, $method, server: ['HTTP_ACCEPT' => 'application/json']));

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
