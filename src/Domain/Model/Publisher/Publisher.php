<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Publisher;

use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherCity;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherFoundationYear;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherName;
use Forkrefactor\Ddd\Domain\Model\AggregateRoot;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class Publisher extends AggregateRoot
{
    private const NAME = 'publisher';
    private Uuid $aggregateId;
    private PublisherName $name;
    private ?PublisherCity $city;
    private Uuid $countryId;
    private ?PublisherFoundationYear $foundationYear;

    public static function create(
        Uuid                     $id,
        PublisherName            $name,
        ?PublisherCity           $city,
        Uuid                     $countryId,
        ?PublisherFoundationYear $foundationYear,
    ): self
    {
        $self = new self($id);
        $self->name = $name;
        $self->city = $city;
        $self->countryId = $countryId;
        $self->foundationYear = $foundationYear;

        return $self;
    }

    public static function hydrate(
        Uuid                     $id,
        PublisherName            $name,
        ?PublisherCity           $city,
        Uuid                     $countryId,
        ?PublisherFoundationYear $foundationYear,
    ): self
    {
        $self = new self($id);
        $self->name = $name;
        $self->city = $city;
        $self->countryId = $countryId;
        $self->foundationYear = $foundationYear;

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

    public function foundationYear(): ?PublisherFoundationYear
    {
        return $this->foundationYear;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id(),
            'name' => $this->name(),
            'city' => $this->city(),
            'countryId' => $this->countryId(),
            'foundationYear' => $this->foundationYear(),
        ];
    }

}