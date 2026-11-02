<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Infrastructure\Behat\Context;

use Assert\Assert;
use Behat\Behat\Context\Context;
use Behat\Step\Given;
use GuzzleHttp\Psr7\Utils;
use Imbo\BehatApiExtension\Context\ApiContext;

final class LibraryApiContext extends ApiContext implements Context
{
    private const LOGIN_PATH = '/api/v1/login_check';

    /**
     * Logs in against the JWT endpoint and sends the token on the next requests.
     *
     * Credentials come from the BEHAT_USER and BEHAT_PASSWORD environment variables.
     */
    #[Given('Given an authentificated user')]
    public function jwtAuthification(): self
    {
        $user = self::env('BEHAT_USER');
        $password = self::env('BEHAT_PASSWORD');

        Assert::that($user)->notEmpty('BEHAT_USER is not set')->email();
        Assert::that($password)->notEmpty('BEHAT_PASSWORD is not set');

        $this->setRequestHeader('Content-Type', 'application/json');
        $this->setRequestBody(json_encode(['username' => $user, 'password' => $password], JSON_THROW_ON_ERROR));
        $this->requestPath(self::LOGIN_PATH, 'POST');

        $body = $this->getResponseBody();
        $token = \is_object($body) ? ($body->token ?? null) : null;

        Assert::that($token)->string('Login did not return a JWT token')->notEmpty();

        $this->request = $this->request->withBody(Utils::streamFor(''));
        $this->setRequestHeader('Authorization', 'Bearer ' . $token);

        return $this;
    }

    private static function env(string $name): string
    {
        $value = $_SERVER[$name] ?? $_ENV[$name] ?? getenv($name);

        return \is_string($value) ? $value : '';
    }
}
