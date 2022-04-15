<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Journal;

use Colybri\Library\Domain\Model\Journal\ValueObject\JournalFoundationYear;
use Colybri\Library\Domain\Model\Journal\ValueObject\JournalImage;
use Colybri\Library\Domain\Model\Journal\ValueObject\JournalName;
use Forkrefactor\Ddd\Domain\Model\AggregateRoot;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class Journal extends AggregateRoot
{
    private const NAME = 'journal';

    private Uuid $aggregateId;
    private JournalName $name;
    private ?JournalImage $image;
    private Uuid $publisherId;
    private ?JournalFoundationYear $foundationYear;

    public static function create(
        Uuid $id,
        JournalName $name,
        ?JournalImage $image,
        Uuid $publisherId,
        ?JournalFoundationYear $foundationYear,
    ): self {
        $self = new self($id);
        $self->name = $name;
        $self->image = $image;
        $self->publisherId = $publisherId;
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

    public function name(): JournalName
    {
        return $this->name;
    }

    public function image(): ?JournalImage
    {
        return $this->image;
    }

    public function publisherId(): Uuid
    {
        return $this->publisherId;
    }

    public function foundationYear(): ?JournalFoundationYear
    {
        return $this->foundationYear;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id(),
            'name' => $this->name(),
            'image' => $this->image(),
            'publisherId' => $this->publisherId(),
            'foundationYear' => $this->foundationYear(),
        ];
    }
}
