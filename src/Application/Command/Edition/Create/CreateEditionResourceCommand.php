<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Edition\Create;

use Assert\Assert;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Book\Book;
use Colybri\Library\Domain\Model\Edition\Edition;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Application\Command;
use PcComponentes\TopicGenerator\Topic;

class CreateEditionResourceCommand extends Command
{
    protected const NAME = 'create_resource';

    protected const VERSION = '1';

    public const EDITION_ID_PAYLOAD = 'id';
    public const EDITION_RESOURCE_PAYLOAD = 'resource';

    public static function messageName(): string
    {
        return Topic::generate(
            CompanyName::instance(),
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
            ->keyExists(self::EDITION_RESOURCE_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::EDITION_ID_PAYLOAD], self::EDITION_ID_PAYLOAD)->uuid()
            ->that($payload[self::EDITION_RESOURCE_PAYLOAD], self::EDITION_RESOURCE_PAYLOAD)->file()
            ->verifyNow();
    }
}
