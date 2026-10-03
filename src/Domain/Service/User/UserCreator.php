<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\User;

use Colybri\Library\Domain\Model\User\Exception\UserAlreadyExistException;
use Colybri\Library\Domain\Model\User\User;
use Colybri\Library\Domain\Model\User\UserRepository;
use Colybri\Library\Domain\Model\User\ValueObject\UserEmail;
use Colybri\Library\Domain\Model\User\ValueObject\UserIsActive;
use Colybri\Library\Domain\Model\User\ValueObject\UserPassword;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class UserCreator
{
    public function __construct(private UserRepository $repository, private PasswordGenerator $passwordGenerator)
    {
    }

    public function execute(Uuid $id, UserEmail $email, UserPassword $password, UserIsActive $isActive): void
    {
        $this->ensureUserDoesntExists($email);

        $user = User::create(
            $id,
            $email,
            $password,
            $isActive
        );
        $encodedPassword = $this->passwordGenerator->generate($user, $password);

        $user->setPassword($encodedPassword);

        $this->repository->insert($user);
    }

    private function ensureUserDoesntExists(UserEmail $email): void
    {
        $user = $this->repository->findByEmail($email);
        if (null !== $user) {
            throw new UserAlreadyExistException(sprintf('User with email %s already exist', $email->value()));
        }
    }
}