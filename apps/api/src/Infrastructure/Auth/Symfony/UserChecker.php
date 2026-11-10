<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Auth\Symfony;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Enforces users.is_active on the login and on every request with a JWT
 * (user_checker of the login and api firewalls in security.yaml), so turning
 * a user inactive also stops the tokens it already has.
 */
final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof AuthenticationProvider && !$user->isActive()) {
            throw new DisabledException('User account is disabled.');
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
    }
}
