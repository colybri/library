<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Book\Delete;

use Assert\Assert;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Application\Command;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\TopicGenerator\Topic;

final class DeleteBookCommand extends Command
{
    public const BOOK_ID_PAYLOAD = 'id';

    protected const NAME = 'delete';
    protected const VERSION = '1';

    private Uuid $bookId;

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
            ->keyExists(self::BOOK_ID_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::BOOK_ID_PAYLOAD], self::BOOK_ID_PAYLOAD)->uuid()
            ->verifyNow();

        $this->bookId = Uuid::from($payload[self::BOOK_ID_PAYLOAD]);
    }

    public function bookId(): Uuid
    {
        return $this->bookId;
    }
}
