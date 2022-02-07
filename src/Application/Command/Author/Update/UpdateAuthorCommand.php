<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Author\Update;

use Assert\Assert;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorName;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Application\Command;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\TopicGenerator\Topic;

final class UpdateAuthorCommand extends Command
{
    protected const NAME = 'update';
    protected const VERSION = '1';

    public const ID_PAYLOAD = 'id';
    public const NAME_PAYLOAD = 'name';
    public const COUNTRY_ID_PAYLOAD = 'countryId';
    public const IS_PSEUDONYM_OF_PAYLOAD = 'isPseudonymOf';
    public const BORN_AT_PAYLOAD = 'bornAt';
    public const DEATH_AT_PAYLOAD = 'deathAt';


    private Uuid $authorId;
    private AuthorName $name;
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
            ->keyExists(self::NAME_PAYLOAD)
            ->keyExists(self::COUNTRY_ID_PAYLOAD)
            ->keyExists(self::IS_PSEUDONYM_OF_PAYLOAD)
            ->keyExists(self::BORN_AT_PAYLOAD)
            ->keyExists(self::DEATH_AT_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::ID_PAYLOAD], self::ID_PAYLOAD)->uuid()
            ->that($payload[self::NAME_PAYLOAD], self::NAME_PAYLOAD)->notEmpty()->string()
            ->that($payload[self::COUNTRY_ID_PAYLOAD], self::COUNTRY_ID_PAYLOAD)->notEmpty()->uuid()
            ->that($payload[self::IS_PSEUDONYM_OF_PAYLOAD], self::IS_PSEUDONYM_OF_PAYLOAD)->nullOr()->uuid()
            ->that($payload[self::BORN_AT_PAYLOAD], self::BORN_AT_PAYLOAD)->notEmpty()->integer()
            ->that($payload[self::DEATH_AT_PAYLOAD], self::DEATH_AT_PAYLOAD)->nullOr()->integer()
            ->verifyNow();

        $this->authorId = Uuid::from($payload[self::ID_PAYLOAD]);
        $this->name = AuthorName::from((string)$payload[self::NAME_PAYLOAD]);
        $this->countryId = Uuid::from((string)$payload[self::COUNTRY_ID_PAYLOAD]);
        $this->isPseudonymOf = null === $payload[self::IS_PSEUDONYM_OF_PAYLOAD] ? null : Uuid::from((string)$payload[self::IS_PSEUDONYM_OF_PAYLOAD]);
        $this->bornAt = AuthorBornAt::from((int)$payload[self::BORN_AT_PAYLOAD]);
        $this->deathAt = null === $payload[self::DEATH_AT_PAYLOAD] ? null : AuthorDeathAt::from((int)$payload[self::DEATH_AT_PAYLOAD]);
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