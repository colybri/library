<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\EditionResource;

use Colybri\Library\Domain\Model\EditionResource\EditionResource;
use Colybri\Library\Domain\Model\EditionResource\EditionResourceRepository;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceFileName;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceMediaType;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceName;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceSize;
use Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\DbalRepository;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;

final class EditionResourceDbalRepository extends DbalRepository implements EditionResourceRepository
{
    public function find(Uuid $id): ?EditionResource
    {
        $row = $this->connectionRead->fetchAssociative(
            'SELECT * FROM edition_resources WHERE id = :id',
            ['id' => $id->value()]
        );

        return false === $row ? null : $this->map($row);
    }

    public function byEdition(Uuid $editionId): array
    {
        $rows = $this->connectionRead->fetchAllAssociative(
            'SELECT * FROM edition_resources WHERE edition_id = :editionId ORDER BY created_at, name',
            ['editionId' => $editionId->value()]
        );

        return array_map(fn (array $row): EditionResource => $this->map($row), $rows);
    }

    public function insert(EditionResource $resource): void
    {
        $this->connectionWrite->executeStatement(
            'INSERT INTO edition_resources (id, edition_id, name, file_name, media_type, size)
             VALUES (:id, :editionId, :name, :fileName, :mediaType, :size)',
            [
                'id' => $resource->aggregateId()->value(),
                'editionId' => $resource->editionId()->value(),
                'name' => $resource->name()->value(),
                'fileName' => $resource->fileName()->value(),
                'mediaType' => $resource->mediaType()->value(),
                'size' => $resource->size()->value(),
            ]
        );
    }

    public function delete(Uuid $id): void
    {
        $this->connectionWrite->executeStatement('DELETE FROM edition_resources WHERE id = :id', ['id' => $id->value()]);
    }

    private function map(array $row): EditionResource
    {
        return EditionResource::hydrate(
            Uuid::from((string) $row['id']),
            Uuid::from((string) $row['edition_id']),
            EditionResourceName::from((string) $row['name']),
            EditionResourceFileName::from((string) $row['file_name']),
            EditionResourceMediaType::from((string) $row['media_type']),
            EditionResourceSize::from((int) $row['size']),
        );
    }
}
