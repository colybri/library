<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Publisher\Create;

use Assert\Assert;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Publisher\Publisher;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherCity;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherFoundationYear;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherName;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Application\Command;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\TopicGenerator\Topic;

final class CreatePublisherCommand extends Command
{
    protected const NAME = 'create';
    protected const VERSION = '1';

    public const PUBLISHER_ID_PAYLOAD = 'id';
    public const PUBLISHER_NAME_PAYLOAD = 'name';
    public const PUBLISHER_CITY_PAYLOAD = 'city';
    public const PUBLISHER_COUNTRY_ID_PAYLOAD = 'countryId';
    public const PUBLISHER_FOUNDATION_PAYLOAD = 'foundation';

    private Uuid $publisherId;
    private PublisherName $name;
    private PublisherCity $city;
    private Uuid $countryId;
    private PublisherFoundationYear $foundation;

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
            ->keyExists(self::PUBLISHER_NAME_PAYLOAD)
            ->keyExists(self::PUBLISHER_CITY_PAYLOAD)
            ->keyExists(self::PUBLISHER_COUNTRY_ID_PAYLOAD)
            ->keyExists(self::PUBLISHER_FOUNDATION_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::PUBLISHER_ID_PAYLOAD], self::PUBLISHER_ID_PAYLOAD)->notEmpty()->uuid()
            ->that($payload[self::PUBLISHER_NAME_PAYLOAD], self::PUBLISHER_NAME_PAYLOAD)->notEmpty()->string()
            ->that($payload[self::PUBLISHER_CITY_PAYLOAD], self::PUBLISHER_CITY_PAYLOAD)->nullOr()->string()
            ->that($payload[self::PUBLISHER_COUNTRY_ID_PAYLOAD], self::PUBLISHER_COUNTRY_ID_PAYLOAD)->notEmpty()->uuid()
            ->that($payload[self::PUBLISHER_FOUNDATION_PAYLOAD], self::PUBLISHER_FOUNDATION_PAYLOAD)->nullOr()->integer()
            ->verifyNow();

        $this->publisherId = Uuid::from($payload[self::PUBLISHER_ID_PAYLOAD]);
        $this->name = PublisherName::from((string)$payload[self::PUBLISHER_NAME_PAYLOAD]);
        $this->city = PublisherCity::from((string)$payload[self::PUBLISHER_CITY_PAYLOAD]);
        $this->countryId = Uuid::from((string)$payload[self::PUBLISHER_COUNTRY_ID_PAYLOAD]);
        $this->foundation = null === $payload[self::PUBLISHER_FOUNDATION_PAYLOAD] ? null : PublisherFoundationYear::from((int)$payload[self::PUBLISHER_FOUNDATION_PAYLOAD]);
        $this->foundation = PublisherFoundationYear::from((int)$payload[self::PUBLISHER_FOUNDATION_PAYLOAD]);
    }


    public function publisherId(): Uuid
    {
        return $this->publisherId;
    }

    public function name(): PublisherName
    {
        return $this->name;
    }

    public function city(): ?PublisherCity
    {
        return $this->city;
    }

    public function countryId(): Uuid
    {
        return $this->countryId;
    }

    public function foundation(): PublisherFoundationYear
    {
        return $this->foundation;
    }
}
