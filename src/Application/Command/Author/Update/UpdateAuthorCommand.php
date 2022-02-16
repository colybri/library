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

    public const AUTHOR_ID_PAYLOAD = 'id';
    public const AUTHOR_NAME_PAYLOAD = 'name';
    public const AUTHOR_COUNTRY_ID_PAYLOAD = 'countryId';
    public const AUTHOR_IS_PSEUDONYM_OF_PAYLOAD = 'isPseudonymOf';
    public const AUTHOR_BORN_YEAR_PAYLOAD = 'bornAt';
    public const AUTHOR_DEATH_YEAR_PAYLOAD = 'deathAt';


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
            ->keyExists(self::AUTHOR_ID_PAYLOAD)
            ->keyExists(self::AUTHOR_NAME_PAYLOAD)
            ->keyExists(self::AUTHOR_COUNTRY_ID_PAYLOAD)
            ->keyExists(self::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD)
            ->keyExists(self::AUTHOR_BORN_YEAR_PAYLOAD)
            ->keyExists(self::AUTHOR_DEATH_YEAR_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::AUTHOR_ID_PAYLOAD], self::AUTHOR_ID_PAYLOAD)->uuid()
            ->that($payload[self::AUTHOR_NAME_PAYLOAD], self::AUTHOR_NAME_PAYLOAD)->notEmpty()->string()
            ->that($payload[self::AUTHOR_COUNTRY_ID_PAYLOAD], self::AUTHOR_COUNTRY_ID_PAYLOAD)->notEmpty()->uuid()
            ->that($payload[self::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD], self::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD)->nullOr()->uuid()
            ->that($payload[self::AUTHOR_BORN_YEAR_PAYLOAD], self::AUTHOR_BORN_YEAR_PAYLOAD)->notEmpty()->integer()
            ->that($payload[self::AUTHOR_DEATH_YEAR_PAYLOAD], self::AUTHOR_DEATH_YEAR_PAYLOAD)->nullOr()->integer()
            ->verifyNow();

        $this->authorId = Uuid::from($payload[self::AUTHOR_ID_PAYLOAD]);
        $this->name = AuthorName::from((string)$payload[self::AUTHOR_NAME_PAYLOAD]);
        $this->countryId = Uuid::from((string)$payload[self::AUTHOR_COUNTRY_ID_PAYLOAD]);
        $this->isPseudonymOf = null === $payload[self::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD] ? null : Uuid::from((string)$payload[self::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD]);
        $this->bornAt = AuthorBornAt::from((int)$payload[self::AUTHOR_BORN_YEAR_PAYLOAD]);
        $this->deathAt = null === $payload[self::AUTHOR_DEATH_YEAR_PAYLOAD] ? null : AuthorDeathAt::from((int)$payload[self::AUTHOR_DEATH_YEAR_PAYLOAD]);
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