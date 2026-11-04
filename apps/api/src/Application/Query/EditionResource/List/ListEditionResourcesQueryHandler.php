<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\EditionResource\List;

use Colybri\Library\Domain\Model\EditionResource\EditionResource;
use Colybri\Library\Domain\Model\EditionResource\EditionResourceRepository;
use Colybri\Library\Domain\Service\Edition\EditionFinder;

final class ListEditionResourcesQueryHandler
{
    public function __construct(
        private readonly EditionResourceRepository $repository,
        private readonly EditionFinder $editionFinder,
    ) {
    }

    /** @return list<EditionResource> */
    public function __invoke(ListEditionResourcesQuery $query): array
    {
        $this->editionFinder->execute($query->editionId());

        return $this->repository->byEdition($query->editionId());
    }
}
