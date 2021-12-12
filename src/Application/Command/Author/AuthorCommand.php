<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Author;

use Assert\Assert;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorFirstName;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorLastName;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Application\Command;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\TopicGenerator\Topic;

class AuthorCommand extends Command
{
    public const ID_PAYLOAD = 'id';
    public const FIRST_NAME_PAYLOAD = 'firstName';
    public const LAST_NAME_PAYLOAD = 'lastName';
    public const COUNTRY_ID_PAYLOAD = 'countryId';
    public const IS_PSEUDONYM_OF_PAYLOAD = 'isPseudonymOf';
    public const BORN_AT_PAYLOAD = 'bornAt';
    public const DEATH_AT_PAYLOAD = 'deathAt';

    protected const NAME = "";
    protected const VERSION ="";

    private Uuid $authorId;
    private AuthorFirstName $firstName;
    private ?AuthorLastName $lastName;
    private Uuid $countryId;
    private ?Uuid $isPseudonymOf;
    private AuthorBornAt $bornAt;
    private ?AuthorDeathAt $deathAt;

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
            ->keyExists(self::ID_PAYLOAD)
            ->keyExists(self::FIRST_NAME_PAYLOAD)
            ->keyExists(self::LAST_NAME_PAYLOAD)
            ->keyExists(self::COUNTRY_ID_PAYLOAD)
            ->keyExists(self::IS_PSEUDONYM_OF_PAYLOAD)
            ->keyExists(self::BORN_AT_PAYLOAD)
            ->keyExists(self::DEATH_AT_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::ID_PAYLOAD], self::ID_PAYLOAD)->uuid()
            ->that($payload[self::FIRST_NAME_PAYLOAD], self::FIRST_NAME_PAYLOAD)->notEmpty()->string()
            ->that($payload[self::LAST_NAME_PAYLOAD], self::LAST_NAME_PAYLOAD)->nullOr()->string()
            ->that($payload[self::COUNTRY_ID_PAYLOAD], self::COUNTRY_ID_PAYLOAD)->notEmpty()->uuid()
            ->that($payload[self::IS_PSEUDONYM_OF_PAYLOAD], self::IS_PSEUDONYM_OF_PAYLOAD)->nullOr()->uuid()
            ->that($payload[self::BORN_AT_PAYLOAD], self::BORN_AT_PAYLOAD)->notEmpty()->date('m/d/Y')
            ->that($payload[self::DEATH_AT_PAYLOAD], self::DEATH_AT_PAYLOAD)->nullOr()->date('m/d/Y')
            ->verifyNow();

        $this->authorId = Uuid::from($payload[self::ID_PAYLOAD]);
        $this->firstName = AuthorFirstName::from((string)$payload[self::FIRST_NAME_PAYLOAD]);
        $this->lastName = AuthorLastName::from((string)$payload[self::LAST_NAME_PAYLOAD]);
        $this->countryId = Uuid::from((string)$payload[self::COUNTRY_ID_PAYLOAD]);
        //TODO ¿como se hace si puede ser nulo?
        $this->isPseudonymOf = null === $payload[self::IS_PSEUDONYM_OF_PAYLOAD] ? null : Uuid::from((string)$payload[self::IS_PSEUDONYM_OF_PAYLOAD]);
        $this->bornAt = AuthorBornAt::from((string)$payload[self::BORN_AT_PAYLOAD]);
        $this->deathAt = null === $payload[self::DEATH_AT_PAYLOAD] ? null : AuthorDeathAt::from((string)$payload[self::DEATH_AT_PAYLOAD]);
    }

    public function authorId(): Uuid
    {
        return $this->authorId;
    }

    public function firstName(): AuthorFirstName
    {
        return $this->firstName;
    }

    public function lastName(): AuthorLastName
    {
        return $this->lastName;
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