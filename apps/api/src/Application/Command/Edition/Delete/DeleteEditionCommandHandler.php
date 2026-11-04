<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Edition\Delete;

use Colybri\Library\Domain\Service\Edition\EditionDeleter;
use Colybri\Library\Domain\Service\EditionResource\EditionResourceDeleter;

final class DeleteEditionCommandHandler
{
    public function __construct(private EditionDeleter $deleter, private EditionResourceDeleter $resourceDeleter)
    {
    }

    public function __invoke(DeleteEditionCommand $cmd): void
    {
        // Its files first: the rows would go with the edition (ON DELETE CASCADE) but the files would stay
        $this->resourceDeleter->ofEdition($cmd->editionId());

        $this->deleter->execute(
            $cmd->editionId(),
        );
    }
}
