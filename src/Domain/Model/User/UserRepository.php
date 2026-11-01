<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\User;

use Colybri\Library\Domain\Model\User\ValueObject\UserEmail;

interface UserRepository
{
    public function insert(User $user): void;

    public function findByEmail(UserEmail $email): ?User;

}