<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Edition;

use Colybri\Library\Domain\Model\Edition\ValueObject\EditionIsOnLibrary;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionYear;
use Forkrefactor\Ddd\Domain\Model\AggregateRoot;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class Edition extends AggregateRoot
{
    private const NAME = 'edition';
    private Uuid $aggregateId;
    private EditionYear $year;
    private Uuid $publisherId;
    private EditionIsOnLibrary $isOnLibrary;


    public static function create(
        Uuid               $id,
        EditionYear        $year,

        Uuid               $publisherId,
        EditionIsOnLibrary $isOnLibrary
    )
    {
        $self = new self($id);
        $self->aggregateId = $id;
        $self->year = $year;
        $self->publisherId = $publisherId;
        $self->isOnLibrary = $isOnLibrary;
    }

    public static function modelName(): string
    {
        return self::NAME;
    }

    public function id(): Uuid
    {
        return $this->aggregateId;
    }

    public function year(): EditionYear
    {
        return $this->year;
    }

    public function publisherId(): Uuid
    {
        return $this->publisherId;
    }

    public function isOnLibrary(): EditionIsOnLibrary
    {
        return $this->isOnLibrary;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id(),
            'year' => $this->year(),
            'isOnLibrary' => $this->isOnLibrary(),
            'publisherId' => $this->publisherId(),
        ];
    }

}