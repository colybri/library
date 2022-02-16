<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Author\Event;

use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorName;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Domain\Model\DomainEvent;
use Forkrefactor\Ddd\Domain\Model\ValueObject\DateTimeValueObject;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\TopicGenerator\Topic;

class AuthorDeleted extends DomainEvent
{
    private const NAME = 'deleted';
    private const VERSION = '1';

    private const AUTHOR_ID = 'id';

    private Uuid $authorId;

    public static function from(Uuid $id): static
    {
        return static::fromPayload(
            Uuid::v4(),
            $id,
            new DateTimeValueObject(),
            [
                self::AUTHOR_ID => $id->value(),
            ]
        );
    }

    protected function assertPayload(): void
    {
        $payload = $this->messagePayload();

        $this->authorId = Uuid::from((string)$payload[self::AUTHOR_ID]);
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

    public function authorId(): Uuid
    {
        return $this->authorId;
    }
}