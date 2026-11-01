<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Auth\Symfony\Service;

use Colybri\Library\Domain\Model\User\User;
use Colybri\Library\Domain\Model\User\ValueObject\UserPassword;
use Colybri\Library\Domain\Service\User\PasswordGenerator;
use Colybri\Library\Infrastructure\Auth\Symfony\AuthenticationProvider;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SymfonyPasswordEncoder implements PasswordGenerator
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }
    public function generate(User $user, UserPassword $password): UserPassword
    {
        $authUser = new AuthenticationProvider($user->email(), $password);
        return UserPassword::from($this->passwordHasher->hashPassword($authUser, $password->value()));
    }

}