<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\EditionResource;

use Colybri\Library\Domain\Model\EditionResource\EditionResource;
use Colybri\Library\Domain\Model\EditionResource\EditionResourceRepository;
use Colybri\Library\Domain\Model\EditionResource\Exception\EditionResourceDoesNotExistException;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;

final class EditionResourceFinder
{
    public function __construct(private readonly EditionResourceRepository $repository)
    {
    }

    /**
     * @throws EditionResourceDoesNotExistException when it does not exist or belongs to another edition
     */
    public function execute(Uuid $id, Uuid $editionId): EditionResource
    {
        $resource = $this->repository->find($id);

        if (null === $resource || false === $resource->editionId()->equalTo($editionId)) {
            throw new EditionResourceDoesNotExistException(
                sprintf('Resource with id: %s does not exist on edition %s', $id->value(), $editionId->value())
            );
        }

        return $resource;
    }
}
