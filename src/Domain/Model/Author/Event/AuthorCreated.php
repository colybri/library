<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Author\Event;

use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorFirstName;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorLastName;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Domain\Model\DomainEvent;
use Forkrefactor\Ddd\Domain\Model\ValueObject\DateTimeValueObject;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\TopicGenerator\Topic;

class AuthorCreated extends DomainEvent
{

    public const ID_PAYLOAD = 'id';
    public const FIRST_NAME_PAYLOAD = 'firstName';
    public const LAST_NAME_PAYLOAD = 'lastName';
    public const COUNTRY_ID_PAYLOAD = 'countryId';
    public const IS_PSEUDONYM_OF_PAYLOAD = 'isPseudonymOf';
    public const BORN_AT_PAYLOAD = 'bornAt';
    public const DEATH_AT_PAYLOAD = 'deathAt';


    private const NAME = 'created';
    private const VERSION = '1';

    private Uuid $authorId;
    private AuthorFirstName $firstName;
    private AuthorLastName $lastName;
    private Uuid $countryId;
    private Uuid $isPseudonymOf;
    private AuthorBornAt $bornAt;
    private AuthorDeathAt $deathAt;

    public static function from(
        Uuid            $id,
        AuthorFirstName $firstName,
        ?AuthorLastName $lastName,
        Uuid            $countryId,
        ?Uuid           $isPseudonymOf,
        AuthorBornAt    $bornAt,
        ?AuthorDeathAt  $deathAt): static
    {
        return static::fromPayload(
            Uuid::v4(),
            $id,
            new DateTimeValueObject(),
            [
                self::FIRST_NAME_PAYLOAD => $firstName->value(),
                self::LAST_NAME_PAYLOAD => $lastName->value(),
                self::COUNTRY_ID_PAYLOAD => $countryId->value(),
                self::IS_PSEUDONYM_OF_PAYLOAD => $isPseudonymOf?->value(),
                self::BORN_AT_PAYLOAD => $bornAt,
                self::DEATH_AT_PAYLOAD => $deathAt,
            ]
        );
    }


    protected function assertPayload(): void
    {
        $payload = $this->messagePayload();

        $this->authorId = Uuid::from((string)$payload[self::ID_PAYLOAD]);
        $this->firstName = AuthorFirstName::from((string)$payload[self::FIRST_NAME_PAYLOAD]);
        $this->lastName = AuthorLastName::from((string)$payload[self::LAST_NAME_PAYLOAD]);
        $this->countryId = Uuid::from((string)$payload[self::COUNTRY_ID_PAYLOAD]);
        $this->isPseudonymOf = null === $payload[self::IS_PSEUDONYM_OF_PAYLOAD] ? null : Uuid::from((string)$payload[self::IS_PSEUDONYM_OF_PAYLOAD]);
        $this->bornAt = AuthorBornAt::from((string)$payload[self::BORN_AT_PAYLOAD]);
        $this->deathAt = null === $payload[self::DEATH_AT_PAYLOAD] ? null : AuthorDeathAt::from((string)$payload[self::DEATH_AT_PAYLOAD]);

    }

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
}