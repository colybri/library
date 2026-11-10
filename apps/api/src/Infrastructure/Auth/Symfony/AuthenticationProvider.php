<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Auth\Symfony;

use Colybri\Library\Domain\Model\User\ValueObject\UserEmail;
use Colybri\Library\Domain\Model\User\ValueObject\UserPassword;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class AuthenticationProvider implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        private UserEmail $email,
        private UserPassword $password,
        private bool $active,
    ) {
    }

    /** users.is_active: UserChecker rejects the login and the JWT of an inactive user */
    public function isActive(): bool
    {
        return $this->active;
    }

    public function getPassword(): string
    {
        return $this->password->value();
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->email->value();
    }
}
