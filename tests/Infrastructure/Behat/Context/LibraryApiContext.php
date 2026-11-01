<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Infrastructure\Behat\Context;

use Assert\Assert;
use Behat\Behat\Context\Context;
use Imbo\BehatApiExtension\Context\ApiContext;

final class LibraryApiContext extends ApiContext implements Context
{

    /*region Description
    public function __construct(private string $user, private string $password)
    {
        Assert::that($user)->notEmpty()->email();
        Assert::that($password)->notEmpty()->string();
    }
    */

    /**
     * Set auth user
     *
     * @return self
     *
     * @Given Given an authentificated user
     */
    public function jwtAuthification()
    {
        $this->request->withHeader('HTTP_Content-Type', 'application/json')->withUri('/v1/login_check')->withBody('{
            "username": ' . $this->user . ',
            "password": ' . $this->password . '
        }')->withMethod('POST');
        $result = $this->sendRequest();
        dd($result);

        $this->addRequestHeader('HTTP_Authorization', 'Bearer ' . $token);

        return $this;
    }
}