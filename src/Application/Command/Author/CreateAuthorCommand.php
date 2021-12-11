<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Author;

use Assert\Assert;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorFirstName;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Forkrefactor\Ddd\Application\Command;
use PcComponentes\TopicGenerator\Topic;

class CreateAuthorCommand extends Command
{
    public const ID_PAYLOAD = 'id';
    public const FIRST_NAME_PAYLOAD = 'first_name';

    private const NAME = 'create';
    private const VERSION = '1';

    private Uuid $authorId;
    private AuthorFirstName $firstName;

    public static function messageName(): string
    {
        return Topic::generate(
            CompanyName::instance(),
            ServiceName::instance(),
            self::messageVersion(),
            self::messageType(),
            Author::modelName(),
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
            ->keyExists(self::ID_PAYLOAD)
            ->keyExists(self::FIRST_NAME_PAYLOAD)
        ->verifyNow();

        Assert::lazy()
            ->that($payload[self::ID_PAYLOAD], self::ID_PAYLOAD)->uuid()
            ->that($payload[self::FIRST_NAME_PAYLOAD], self::FIRST_NAME_PAYLOAD)->notEmpty()->string()
            ->verifyNow();

        $this->authorId = Uuid::from($payload[self::ID_PAYLOAD]);
        $this->firstName =  AuthorFirstName::from((string) $payload[self::FIRST_NAME_PAYLOAD]);
    }

    public function authorId(): Uuid
    {
        return $this->authorId;
    }

    public function firstName(): AuthorFirstName
    {
        return $this->firstName;
    }

}