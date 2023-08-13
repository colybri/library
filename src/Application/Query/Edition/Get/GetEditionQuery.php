<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Edition\Get;

use Assert\Assert;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Edition\Edition;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Application\Query;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\TopicGenerator\Topic;

class GetEditionQuery extends Query
{
    private const VERSION = '1';
    private const NAME = 'get';

    public const EDITION_ID_PAYLOAD = 'id';
    private Uuid $editionId;

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
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::EDITION_ID_PAYLOAD], self::EDITION_ID_PAYLOAD)->notEmpty()->uuid()
            ->verifyNow();

        $this->editionId = Uuid::from($payload[self::EDITION_ID_PAYLOAD]);
    }

    public function editionId(): Uuid
    {
        return $this->editionId;
    }
}
