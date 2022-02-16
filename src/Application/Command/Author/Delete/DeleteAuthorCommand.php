<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Author\Delete;

use Assert\Assert;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Application\Command;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\TopicGenerator\Topic;

final class DeleteAuthorCommand extends Command
{
    public const AUTHOR_ID_PAYLOAD = 'id';

    protected const NAME = 'delete';
    protected const VERSION = '1';

    private Uuid $authorId;

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
            ->keyExists(self::AUTHOR_ID_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::AUTHOR_ID_PAYLOAD], self::AUTHOR_ID_PAYLOAD)->uuid()
            ->verifyNow();

        $this->authorId = Uuid::from($payload[self::AUTHOR_ID_PAYLOAD]);
    }

    public function authorId(): Uuid
    {
        return $this->authorId;
    }
}