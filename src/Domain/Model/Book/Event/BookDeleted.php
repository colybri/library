<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Book\Event;

use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Domain\Model\DomainEvent;
use Forkrefactor\Ddd\Domain\Model\ValueObject\DateTimeValueObject;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\TopicGenerator\Topic;

final class BookDeleted extends DomainEvent
{
    private const NAME = 'deleted';
    private const VERSION = '1';

    private const BOOK_ID_PAYLOAD = 'id';

    private Uuid $bookId;

    public static function from(Uuid $id): static
    {
        return static::fromPayload(
            Uuid::v4(),
            $id,
            new DateTimeValueObject(),
            [
                self::BOOK_ID_PAYLOAD => $id->value(),
            ]
        );
    }

    protected function assertPayload(): void
    {
        $payload = $this->messagePayload();

        $this->bookId = Uuid::from((string)$payload[self::BOOK_ID_PAYLOAD]);
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

    public function bookId(): Uuid
    {
        return $this->bookId;
    }
}
