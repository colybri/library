<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Author\Event;

use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorName;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Domain\Model\DomainEvent;
use Forkrefactor\Ddd\Domain\Model\ValueObject\DateTimeValueObject;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\TopicGenerator\Topic;

final class AuthorCreated extends DomainEvent
{
    public const AUTHOR_ID_PAYLOAD = 'id';
    public const AUTHOR_NAME_PAYLOAD = 'name';
    public const AUTHOR_COUNTRY_ID_PAYLOAD = 'countryId';
    public const AUTHOR_IS_PSEUDONYM_OF_PAYLOAD = 'isPseudonymOf';
    public const AUTHOR_BORN_AT_PAYLOAD = 'bornAt';
    public const AUTHOR_DEATH_AT_PAYLOAD = 'deathAt';


    private const NAME = 'created';
    private const VERSION = '1';

    private Uuid $authorId;
    private AuthorName $name;
    private Uuid $countryId;
    private ?Uuid $isPseudonymOf;
    private AuthorBornAt $bornAt;
    private ?AuthorDeathAt $deathAt;

    public static function from(
        Uuid $id,
        AuthorName $name,
        Uuid $countryId,
        ?Uuid $isPseudonymOf,
        AuthorBornAt $bornAt,
        ?AuthorDeathAt $deathAt
    ): static {
        return static::fromPayload(
            Uuid::v4(),
            $id,
            new DateTimeValueObject(),
            [
                self::AUTHOR_ID_PAYLOAD => $id->value(),
                self::AUTHOR_NAME_PAYLOAD => $name->value(),
                self::AUTHOR_COUNTRY_ID_PAYLOAD => $countryId->value(),
                self::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD => $isPseudonymOf?->value(),
                self::AUTHOR_BORN_AT_PAYLOAD => $bornAt->value(),
                self::AUTHOR_DEATH_AT_PAYLOAD => $deathAt?->value(),
            ]
        );
    }


    protected function assertPayload(): void
    {
        $payload = $this->messagePayload();

        $this->authorId = Uuid::from((string)$payload[self::AUTHOR_ID_PAYLOAD]);
        $this->name = AuthorName::from((string)$payload[self::AUTHOR_NAME_PAYLOAD]);
        $this->countryId = Uuid::from((string)$payload[self::AUTHOR_COUNTRY_ID_PAYLOAD]);
        $this->isPseudonymOf = null === $payload[self::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD]
            ? null : Uuid::from((string)$payload[self::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD]);
        $this->bornAt = AuthorBornAt::from((int)$payload[self::AUTHOR_BORN_AT_PAYLOAD]);
        $this->deathAt = null === $payload[self::AUTHOR_DEATH_AT_PAYLOAD]
            ? null : AuthorDeathAt::from((int)$payload[self::AUTHOR_DEATH_AT_PAYLOAD]);
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

    public function authorId(): Uuid
    {
        return $this->authorId;
    }

    public function name(): AuthorName
    {
        return $this->name;
    }

    public function countryId(): Uuid
    {
        return $this->countryId;
    }

    public function bornAt(): AuthorBornAt
    {
        return $this->bornAt;
    }

    public function deathAt(): ?AuthorDeathAt
    {
        return $this->deathAt;
    }

    public function isPseudonymOf(): ?Uuid
    {
        return $this->isPseudonymOf;
    }
}
