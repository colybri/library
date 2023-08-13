<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Author;

use Colybri\Library\Domain\Model\Author\Event\AuthorDeleted;
use Colybri\Library\Domain\Model\Author\Event\AuthorUpdated;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorLastName;
use Forkrefactor\Ddd\Domain\Model\SimpleAggregateRoot;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Colybri\Library\Domain\Model\Author\Event\AuthorCreated;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorFirstName;
use JetBrains\PhpStorm\Pure;

final class Author extends SimpleAggregateRoot
{
    private const NAME = 'author';
    private AuthorFirstName $name;
    private ?AuthorLastName $lastName;
    private Uuid $countryId;
    private ?Uuid $isPseudonymOf;
    private AuthorBornAt $bornAt;
    private ?AuthorDeathAt $deathAt;

    public static function create(
        Uuid $id,
        AuthorFirstName $name,
        ?AuthorLastName $lastName,
        Uuid $countryId,
        ?Uuid $isPseudonymOf,
        AuthorBornAt $bornAt,
        ?AuthorDeathAt $deathAt
    ): self {
        $self = new self($id);
        $self->name = $name;
        $self->lastName = $lastName;
        $self->countryId = $countryId;
        $self->isPseudonymOf = $isPseudonymOf;
        $self->bornAt = $bornAt;
        $self->deathAt = $deathAt;

        $self->recordThat(AuthorCreated::from($id, $name, $lastName, $countryId, $isPseudonymOf, $bornAt, $deathAt));
        return $self;
    }

    public static function reconstitute(
        Uuid $id,
        AuthorFirstName $firstName,
        ?AuthorLastName $lastName,
        Uuid $countryId,
        ?Uuid $isPseudonymOf,
        AuthorBornAt $bornAt,
        ?AuthorDeathAt $deathAt
    ): self {
        $self = new self($id);
        $self->name = $firstName;
        $self->lastName = $lastName;
        $self->countryId = $countryId;
        $self->isPseudonymOf = $isPseudonymOf;
        $self->bornAt = $bornAt;
        $self->deathAt = $deathAt;

        return $self;
    }

    public function delete(AuthorRepository $repository)
    {
        $repository->delete($this->aggregateId());

        $this->recordThat(AuthorDeleted::from($this->aggregateId()));
    }

    public static function modelName(): string
    {
        return self::NAME;
    }

    public function firstName(): AuthorFirstName
    {
        return $this->name;
    }

    public function lastName(): ?AuthorLastName
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

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->aggregateId(),
            'firstName' => $this->firstName(),
            'lastName' => $this->lastName(),
            'countryId' => $this->countryId(),
            'isPseudonymOf' => $this->isPseudonymOf(),
            'bornAt' => $this->bornAt(),
            'deathAt' => $this->deathAt(),
        ];
    }
}
