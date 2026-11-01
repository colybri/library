<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Publisher\Get;

use Assert\Assert;
use Colybri\Library\Application\Query\Query;
use Colybri\Library\Domain\Messaging\Message\ValueObject\MessageName;
use Colybri\Library\Domain\VendorName;
use Colybri\Library\Domain\ServiceName;
use Colybri\Library\Domain\Model\Publisher\Publisher;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class GetPublisherQuery extends Query
{
    protected const NAME = 'get';
    protected const VERSION = '1';

    public const PUBLISHER_ID_PAYLOAD = 'id';

    private Uuid $publisherId;

    public static function messageName(): string
    {
        return MessageName::generate(
            VendorName::instance(),
            ServiceName::instance(),
            self::messageVersion(),
            self::messageType(),
            Publisher::modelName(),
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
            ->keyExists(self::PUBLISHER_ID_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::PUBLISHER_ID_PAYLOAD], self::PUBLISHER_ID_PAYLOAD)->notEmpty()->uuid()
            ->verifyNow();

        $this->publisherId = Uuid::from($payload[self::PUBLISHER_ID_PAYLOAD]);
    }

    public function publisherId(): Uuid
    {
        return $this->publisherId;
    }
}
