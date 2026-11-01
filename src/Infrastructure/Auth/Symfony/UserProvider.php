<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Auth\Symfony;

use Colybri\Library\Domain\Model\User\UserRepository;
use Colybri\Library\Domain\Model\User\ValueObject\UserEmail;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function __construct(private UserRepository $repository)
    {
    }
    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class)
    {
        return AuthenticationProvider::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->repository->findByEmail(UserEmail::from($identifier));

        if (null === $user) {
            throw new UserNotFoundException();
        }

        return new AuthenticationProvider($user->email(), $user->password());
    }

}