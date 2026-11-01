<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\User;

use Colybri\Library\Domain\Model\User\User;
use Colybri\Library\Domain\Model\User\UserRepository;
use Colybri\Library\Domain\Model\User\ValueObject\UserEmail;
use Colybri\Library\Domain\Model\User\ValueObject\UserIsActive;
use Colybri\Library\Domain\Model\User\ValueObject\UserPassword;
use Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\DbalRepository;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class UserDbalRepository extends DbalRepository implements UserRepository
{

    public function findByEmail(UserEmail $email): ?User
    {
        $sql = "
            SELECT * from users 
             WHERE (
                email = :email
            );
        ";

        $query = $this->connectionRead->prepare($sql);
        $query->bindValue('email', $email->value());

        $user = $query->executeQuery()->fetchAssociative();

        if (false === $user) {
            return null;
        }

        return $this->map($user);
    }

    public function insert(User $user): void
    {
        $sql = "
            INSERT into users (
                id, 
                email,
                password,
                is_active                 
            ) VALUES (
                :id,
                :email,
                :password,
                :isActive              
            );
        ";

        $statement = $this->connectionWrite->prepare($sql);
        $statement->bindValue('id', $user->aggregateId()->value());
        $statement->bindValue('email', $user->email()->value());
        $statement->bindValue('password', $user->password()?->value());
        $statement->bindValue('isActive', $user->isActive()->value());

        $statement->executeQuery();
    }

    private function map(array $user): User
    {
        return User::hydrate(
            Uuid::from($user['id']),
            UserEmail::from($user['email']),
            UserPassword::from($user['password']),
            UserIsActive::from($user['is_active'])
        );
    }

}