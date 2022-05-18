<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Edition\Delete;

use Assert\Assert;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Application\Command;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\TopicGenerator\Topic;

final class DeleteEditionCommand extends Command
{
    public const EDITION_ID_PAYLOAD = 'id';

    protected const NAME = 'delete';
    protected const VERSION = '1';

    private Uuid $editionId;

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
            ->keyExists(self::EDITION_ID_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::EDITION_ID_PAYLOAD], self::EDITION_ID_PAYLOAD)->uuid()
            ->verifyNow();

        $this->editionId = Uuid::from($payload[self::EDITION_ID_PAYLOAD]);
    }

    public function editionId(): Uuid
    {
        return $this->editionId;
    }
}
