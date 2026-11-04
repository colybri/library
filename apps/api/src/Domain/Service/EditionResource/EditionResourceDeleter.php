<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\EditionResource;

use Colybri\Library\Domain\Model\EditionResource\EditionResource;
use Colybri\Library\Domain\Model\EditionResource\EditionResourceRepository;
use Colybri\Library\Domain\Model\EditionResource\EditionResourceStorage;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;

final class EditionResourceDeleter
{
    public function __construct(
        private readonly EditionResourceRepository $repository,
        private readonly EditionResourceStorage $storage,
        private readonly EditionResourceFinder $finder,
    ) {
    }

    public function execute(Uuid $id, Uuid $editionId): EditionResource
    {
        $resource = $this->finder->execute($id, $editionId);

        $this->remove($resource);

        return $resource;
    }

    /** Removes every resource of an edition, before the edition itself is deleted */
    public function ofEdition(Uuid $editionId): void
    {
        foreach ($this->repository->byEdition($editionId) as $resource) {
            $this->remove($resource);
        }
    }

    private function remove(EditionResource $resource): void
    {
        $this->repository->delete($resource->aggregateId());
        $this->storage->delete($resource);
    }
}
