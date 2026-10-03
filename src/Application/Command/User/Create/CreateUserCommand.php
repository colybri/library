<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\User\Create;

use Assert\Assert;
use Colybri\Library\Application\Command\Command;
use Colybri\Library\Domain\Messaging\Message\ValueObject\MessageName;
use Colybri\Library\Domain\VendorName;
use Colybri\Library\Domain\ServiceName;
use Colybri\Library\Domain\Model\User\User;
use Colybri\Library\Domain\Model\User\ValueObject\UserEmail;
use Colybri\Library\Domain\Model\User\ValueObject\UserIsActive;
use Colybri\Library\Domain\Model\User\ValueObject\UserPassword;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class CreateUserCommand extends Command
{
    protected const NAME = 'create';
    protected const VERSION = '1';

    public const USER_ID_PAYLOAD = 'id';
    public const USER_EMAIL_PAYLOAD = 'email';
    public const USER_PASSWORD_PAYLOAD = 'password';
    public const USER_IS_ACTIVE_PAYLOAD = 'isActive';

    private Uuid $userId;
    private UserEmail $email;
    private UserPassword $password;
    private UserIsActive $isActive;
    public static function messageName(): string
    {
        return MessageName::generate(
            VendorName::instance(),
            ServiceName::instance(),
            self::messageVersion(),
            self::messageType(),
            User::modelName(),
            self::NAME
        );
    }

    public static function messageVersion(): string
    {
        return self::VERSION;
    }

    protected function assertPayload(): void
    {
        $payload = $this->messagePayload();

        Assert::lazy()
            ->that($payload, 'payload')->isArray()
            ->keyExists(self::USER_ID_PAYLOAD)
            ->keyExists(self::USER_EMAIL_PAYLOAD)
            ->keyExists(self::USER_PASSWORD_PAYLOAD)
            ->keyExists(self::USER_IS_ACTIVE_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::USER_ID_PAYLOAD], self::USER_ID_PAYLOAD)->notEmpty()->uuid()
            ->that($payload[self::USER_EMAIL_PAYLOAD], self::USER_EMAIL_PAYLOAD)->notEmpty()->string()
            ->that($payload[self::USER_PASSWORD_PAYLOAD], self::USER_PASSWORD_PAYLOAD)->notEmpty()->string()
            ->that($payload[self::USER_IS_ACTIVE_PAYLOAD], self::USER_IS_ACTIVE_PAYLOAD)->notEmpty()->boolean()
            ->verifyNow();

        $this->userId = Uuid::from($payload[self::USER_ID_PAYLOAD]);
        $this->email = UserEmail::from((string)$payload[self::USER_EMAIL_PAYLOAD]);
        $this->password = UserPassword::from((string)$payload[self::USER_PASSWORD_PAYLOAD]);
        $this->isActive = UserIsActive::from((bool)$payload[self::USER_IS_ACTIVE_PAYLOAD]);
    }


    public function userId(): Uuid
    {
        return $this->userId;
    }

    public function email(): UserEmail
    {
        return $this->email;
    }

    public function password(): ?UserPassword
    {
        return $this->password;
    }

    public function isActive(): UserIsActive
    {
        return $this->isActive;
    }
}