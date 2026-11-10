<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Infrastructure\Auth\Symfony;

use Colybri\Library\Domain\Model\User\ValueObject\UserEmail;
use Colybri\Library\Domain\Model\User\ValueObject\UserPassword;
use Colybri\Library\Infrastructure\Auth\Symfony\AuthenticationProvider;
use Colybri\Library\Infrastructure\Auth\Symfony\UserChecker;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\DisabledException;

final class UserCheckerTest extends TestCase
{
    #[Test]
    public function an_inactive_user_cannot_authenticate(): void
    {
        $this->expectException(DisabledException::class);

        (new UserChecker())->checkPreAuth($this->user(false));
    }

    #[Test]
    public function an_active_user_can_authenticate(): void
    {
        $checker = new UserChecker();
        $checker->checkPreAuth($this->user(true));
        $checker->checkPostAuth($this->user(true));

        $this->addToAssertionCount(1);
    }

    private function user(bool $active): AuthenticationProvider
    {
        return new AuthenticationProvider(UserEmail::from('dummy@example.test'), UserPassword::from('hash'), $active);
    }
}
