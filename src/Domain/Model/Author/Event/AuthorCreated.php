<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Author\Event;

use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorFirstName;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Domain\Model\DomainEvent;
use Forkrefactor\Ddd\Domain\Model\ValueObject\DateTimeValueObject;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\TopicGenerator\Topic;

class AuthorCreated extends DomainEvent
{

    public const ID_PAYLOAD = 'id';
    public const FIRST_NAME_PAYLOAD = 'firstName';

    private const NAME = 'created';
    private const VERSION = '1';

    private Uuid $authorId;
    private AuthorFirstName $firstName;


    public static function from(Uuid $authorId, AuthorFirstName $firstName): static
    {
        return static::fromPayload(
            Uuid::v4(),
            $authorId,
            new DateTimeValueObject(),
            [
                self::FIRST_NAME_PAYLOAD => $firstName->value()
            ]
        );
    }


    protected function assertPayload(): void
    {
        $payload = $this->messagePayload();

        $this->authorId = Uuid::from($payload[self::ID_PAYLOAD]);
        $this->firstName =  AuthorFirstName::from((string) $payload[self::FIRST_NAME_PAYLOAD]);
    }

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
}