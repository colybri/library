<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Country\Get;

use Assert\Assert;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Application\Query;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\TopicGenerator\Topic;

final class GetCountryQuery extends Query
{
    protected const NAME = 'get';
    protected const VERSION = '1';

    public const COUNTRY_ID_PAYLOAD = 'id';

    private Uuid $countryId;

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
            ->keyExists(self::COUNTRY_ID_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::COUNTRY_ID_PAYLOAD], self::COUNTRY_ID_PAYLOAD)->notEmpty()->uuid()
            ->verifyNow();

        $this->countryId = Uuid::from($payload[self::COUNTRY_ID_PAYLOAD]);
    }

    public function countryId(): Uuid
    {
        return $this->countryId;
    }
}
