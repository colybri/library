<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\EditionResource;

use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;

interface EditionResourceRepository
{
    public function find(Uuid $id): ?EditionResource;

    /** @return list<EditionResource> */
    public function byEdition(Uuid $editionId): array;

    public function insert(EditionResource $resource): void;

    public function delete(Uuid $id): void;
}
