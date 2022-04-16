<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Publisher\Delete;

use Assert\Assert;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Publisher\Publisher;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Application\Command;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\TopicGenerator\Topic;

final class DeletePublisherCommand extends Command
{
    public const PUBLISHER_ID_PAYLOAD = 'id';

    protected const NAME = 'delete';
    protected const VERSION = '1';

    private Uuid $publisher;

    public static function messageName(): string
    {
        return Topic::generate(
            CompanyName::instance(),
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
            ->that($payload[self::PUBLISHER_ID_PAYLOAD], self::PUBLISHER_ID_PAYLOAD)->uuid()
            ->verifyNow();

        $this->publisher = Uuid::from($payload[self::PUBLISHER_ID_PAYLOAD]);
    }

    public function publisherId(): Uuid
    {
        return $this->publisher;
    }
}
