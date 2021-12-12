<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Author;

use Forkrefactor\Ddd\Domain\Model\AggregateRoot;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Colybri\Library\Domain\Model\Author\Event\AuthorCreated;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorFirstName;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorLastName;


final class Author extends AggregateRoot
{
    private const NAME = 'author';
    private Uuid $aggregateId;
    private AuthorFirstName $firstName;
    private ?AuthorLastName $lastName;
    private Uuid $countryId;
    private ?Uuid $isPseudonymOf;
    private AuthorBornAt $bornAt;
    private ?AuthorDeathAt $deathAt;

    public static function create(
        Uuid                $id,
        AuthorFirstName     $firstName,
        ?AuthorLastName      $lastName,
        Uuid     $countryId,
        ?Uuid $isPseudonymOf,
        AuthorBornAt        $bornAt,
        ?AuthorDeathAt       $deathAt
    ): self
    {
        $self = new self($id);
        $self->firstName = $firstName;
        $self->lastName = $lastName;
        $self->countryId = $countryId;
        $self->isPseudonymOf = $isPseudonymOf;
        $self->bornAt = $bornAt;
        $self->deathAt = $deathAt;

        $self->recordThat(AuthorCreated::from($id, $firstName));
        return $self;
    }

    public static function modelName(): string
    {
        return self::NAME;
    }

    public function id(): Uuid
    {
        return $this->aggregateId;
    }

    public function firstName(): AuthorFirstName
    {
        return $this->firstName;
    }

    public function lastName():? AuthorLastName
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

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id(),
            'firstName' => $this->firstName(),
            'lastName' => $this->lastName(),
            'countryId' => $this->countryId(),
            'isPseudonymOf' => $this->isPseudonymOf(),
            'bornAt' => $this->bornAt()->format('m/d/Y'),
            'deathAt' => $this->deathAt()?->format('m/d/Y'),
        ];
    }

}