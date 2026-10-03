<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\User;

use Colybri\Library\Domain\Model\User\User;
use Colybri\Library\Domain\Model\User\ValueObject\UserPassword;

interface PasswordGenerator
{
    public function generate(User $user, UserPassword $password): UserPassword;

}