<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\User;

use Colybri\Library\Domain\Model\SimpleAggregateRoot;
use Colybri\Library\Domain\Model\User\ValueObject\UserEmail;
use Colybri\Library\Domain\Model\User\ValueObject\UserIsActive;
use Colybri\Library\Domain\Model\User\ValueObject\UserPassword;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class User extends SimpleAggregateRoot
{
    private const NAME = 'user';
    private UserEmail $email;
    private UserPassword $password;
    private UserIsActive $isActive;

    public static function create(
        Uuid $id,
        UserEmail $email,
        UserPassword $password,
        UserIsActive $isActive
    ): self {
        $self = new self($id);
        $self->email = $email;
        $self->password = $password;
        $self->isActive = $isActive;
        return $self;
    }

    public static function hydrate(
        Uuid $id,
        UserEmail $email,
        UserPassword $password,
        UserIsActive $isActive
    ): self {
        $self = new self($id);
        $self->email = $email;
        $self->password = $password;
        $self->isActive = $isActive;
        return $self;
    }
    public static function modelName(): string
    {
        return self::NAME;
    }

    public function email(): UserEmail
    {
        return $this->email;
    }

    public function password(): UserPassword
    {
        return $this->password;
    }
    public function setPassword(UserPassword $password): void
    {
        $this->password = $password;
    }
    public function isActive(): UserIsActive
    {
        return $this->isActive;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->aggregateId(),
            'email' => $this->email(),
            'password' => $this->password(),
            'isActive' => $this->isActive(),
        ];
    }
}