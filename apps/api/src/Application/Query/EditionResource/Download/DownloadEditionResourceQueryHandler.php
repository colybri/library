<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\EditionResource\Download;

use Colybri\Library\Domain\Model\EditionResource\EditionResourceStorage;
use Colybri\Library\Domain\Service\EditionResource\EditionResourceFinder;

final class DownloadEditionResourceQueryHandler
{
    public function __construct(
        private readonly EditionResourceFinder $finder,
        private readonly EditionResourceStorage $storage,
    ) {
    }

    public function __invoke(DownloadEditionResourceQuery $query): EditionResourceFile
    {
        $resource = $this->finder->execute($query->resourceId(), $query->editionId());

        return new EditionResourceFile($resource, $this->storage->read($resource));
    }
}
