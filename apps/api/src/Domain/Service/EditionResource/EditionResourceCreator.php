<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\EditionResource;

use Colybri\Library\Domain\Model\EditionResource\EditionResource;
use Colybri\Library\Domain\Model\EditionResource\EditionResourceRepository;
use Colybri\Library\Domain\Model\EditionResource\EditionResourceStorage;
use Colybri\Library\Domain\Model\EditionResource\Exception\EditionResourceAlreadyExistException;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceFileName;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceMediaType;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceName;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceSize;
use Colybri\Library\Domain\Service\Edition\EditionFinder;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;

final class EditionResourceCreator
{
    public function __construct(
        private readonly EditionResourceRepository $repository,
        private readonly EditionResourceStorage $storage,
        private readonly EditionFinder $editionFinder,
    ) {
    }

    /** @param string $path Local file with the content, for example the uploaded temporary file */
    public function execute(
        Uuid $id,
        Uuid $editionId,
        EditionResourceName $name,
        EditionResourceFileName $fileName,
        EditionResourceMediaType $mediaType,
        EditionResourceSize $size,
        string $path,
    ): EditionResource {
        $this->editionFinder->execute($editionId);

        if (null !== $this->repository->find($id)) {
            throw new EditionResourceAlreadyExistException(sprintf('Resource with id: %s already exists', $id->value()));
        }

        $resource = EditionResource::create($id, $editionId, $name, $fileName, $mediaType, $size);

        // File first: a row without its file would break the download, while a
        // file without its row is only wasted space.
        $this->storage->save($resource, $path);
        $this->repository->insert($resource);

        return $resource;
    }
}
