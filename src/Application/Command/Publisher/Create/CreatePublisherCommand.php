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

    public const ID_PAYLOAD = 'id';
    public const NAME_PAYLOAD = 'name';
    public const CITY_PAYLOAD = 'city';
    public const COUNTRY_ID_PAYLOAD = 'countryId';
    public const FOUNDATION_PAYLOAD = 'foundation';

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
            ->keyExists(self::ID_PAYLOAD)
            ->keyExists(self::NAME_PAYLOAD)
            ->keyExists(self::CITY_PAYLOAD)
            ->keyExists(self::COUNTRY_ID_PAYLOAD)
            ->keyExists(self::FOUNDATION_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::ID_PAYLOAD], self::ID_PAYLOAD)->uuid()
            ->that($payload[self::NAME_PAYLOAD], self::NAME_PAYLOAD)->notEmpty()->string()
            ->that($payload[self::CITY_PAYLOAD], self::CITY_PAYLOAD)->notEmpty()->string()
            ->that($payload[self::COUNTRY_ID_PAYLOAD], self::COUNTRY_ID_PAYLOAD)->notEmpty()->uuid()
            ->that($payload[self::FOUNDATION_PAYLOAD], self::FOUNDATION_PAYLOAD)->notEmpty()->integer()
            ->verifyNow();

        $this->publisherId = Uuid::from($payload[self::ID_PAYLOAD]);
        $this->name = PublisherName::from((string)$payload[self::NAME_PAYLOAD]);
        $this->city = PublisherCity::from((string)$payload[self::CITY_PAYLOAD]);
        $this->countryId = Uuid::from((string)$payload[self::COUNTRY_ID_PAYLOAD]);
        $this->foundation = PublisherFoundationYear::from((int)$payload[self::FOUNDATION_PAYLOAD]);
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
