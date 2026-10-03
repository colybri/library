<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\User;

use Colybri\Criteria\Infrastructure\Adapter\EntityMap;
use Colybri\Library\Domain\Model\User\ValueObject\UserEmail;
use Colybri\Library\Domain\Model\User\ValueObject\UserIsActive;
use Colybri\Library\Domain\Model\User\ValueObject\UserPassword;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class UserDbalMap implements EntityMap
{
    private const FIELDS = [
        Uuid::class => 'id',
        UserEmail::class => 'email',
        UserPassword::class => 'password',
        UserIsActive::class => 'is_active'
    ];

    private const TABLE = 'users';

    public function map(string $attribute): string
    {
        return self::FIELDS[$attribute];
    }

    public static function table(): string
    {
        return self::TABLE;
    }

}