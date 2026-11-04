<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\EditionResource\Delete;

use Assert\Assert;
use Colybri\Library\Application\Command\Command;
use Colybri\Library\Domain\Messaging\Message\ValueObject\MessageName;
use Colybri\Library\Domain\Model\EditionResource\EditionResource;
use Colybri\Library\Domain\ServiceName;
use Colybri\Library\Domain\VendorName;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;

final class DeleteEditionResourceCommand extends Command
{
    protected const NAME = 'delete';
    protected const VERSION = '1';

    public const RESOURCE_ID_PAYLOAD = 'id';
    public const EDITION_ID_PAYLOAD = 'editionId';

    private Uuid $resourceId;
    private Uuid $editionId;

    public static function messageName(): string
    {
        return MessageName::generate(
            VendorName::instance(),
            ServiceName::instance(),
            self::messageVersion(),
            self::messageType(),
            EditionResource::modelName(),
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
            ->keyExists(self::RESOURCE_ID_PAYLOAD)
            ->keyExists(self::EDITION_ID_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::RESOURCE_ID_PAYLOAD], self::RESOURCE_ID_PAYLOAD)->notEmpty()->uuid()
            ->that($payload[self::EDITION_ID_PAYLOAD], self::EDITION_ID_PAYLOAD)->notEmpty()->uuid()
            ->verifyNow();

        $this->resourceId = Uuid::from((string) $payload[self::RESOURCE_ID_PAYLOAD]);
        $this->editionId = Uuid::from((string) $payload[self::EDITION_ID_PAYLOAD]);
    }

    public function resourceId(): Uuid
    {
        return $this->resourceId;
    }

    public function editionId(): Uuid
    {
        return $this->editionId;
    }
}
