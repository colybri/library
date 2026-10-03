<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Edition\Delete;

use Assert\Assert;
use Colybri\Library\Application\Command\Command;
use Colybri\Library\Domain\Messaging\Message\ValueObject\MessageName;
use Colybri\Library\Domain\VendorName;
use Colybri\Library\Domain\ServiceName;
use Colybri\Library\Domain\Model\Edition\Edition;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class DeleteEditionCommand extends Command
{
    public const EDITION_ID_PAYLOAD = 'id';

    protected const NAME = 'delete';
    protected const VERSION = '1';

    private Uuid $editionId;

    public static function messageName(): string
    {
        return MessageName::generate(
            VendorName::instance(),
            ServiceName::instance(),
            self::messageVersion(),
            self::messageType(),
            Edition::modelName(),
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
