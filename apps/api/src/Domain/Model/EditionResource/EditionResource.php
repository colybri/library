<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\EditionResource;

use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceFileName;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceMediaType;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceName;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceSize;
use Colybri\Library\Domain\Model\SimpleAggregateRoot;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;

/**
 * A file attached to an edition: a digital copy, an index, notes, photos...
 */
final class EditionResource extends SimpleAggregateRoot
{
    private const NAME = 'edition_resource';

    private Uuid $editionId;
    private EditionResourceName $name;
    private EditionResourceFileName $fileName;
    private EditionResourceMediaType $mediaType;
    private EditionResourceSize $size;

    public static function create(
        Uuid $id,
        Uuid $editionId,
        EditionResourceName $name,
        EditionResourceFileName $fileName,
        EditionResourceMediaType $mediaType,
        EditionResourceSize $size,
    ): self {
        return self::hydrate($id, $editionId, $name, $fileName, $mediaType, $size);
    }

    public static function hydrate(
        Uuid $id,
        Uuid $editionId,
        EditionResourceName $name,
        EditionResourceFileName $fileName,
        EditionResourceMediaType $mediaType,
        EditionResourceSize $size,
    ): self {
        $self = new self($id);
        $self->editionId = $editionId;
        $self->name = $name;
        $self->fileName = $fileName;
        $self->mediaType = $mediaType;
        $self->size = $size;

        return $self;
    }

    public static function modelName(): string
    {
        return self::NAME;
    }

    /** Key of the file in the storage */
    public function storageKey(): string
    {
        return sprintf('editions/%s/%s', $this->editionId->value(), $this->aggregateId()->value());
    }

    public function editionId(): Uuid
    {
        return $this->editionId;
    }

    public function name(): EditionResourceName
    {
        return $this->name;
    }

    public function fileName(): EditionResourceFileName
    {
        return $this->fileName;
    }

    public function mediaType(): EditionResourceMediaType
    {
        return $this->mediaType;
    }

    public function size(): EditionResourceSize
    {
        return $this->size;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->aggregateId(),
            'editionId' => $this->editionId(),
            'name' => $this->name(),
            'fileName' => $this->fileName(),
            'mediaType' => $this->mediaType(),
            'size' => $this->size(),
        ];
    }
}
