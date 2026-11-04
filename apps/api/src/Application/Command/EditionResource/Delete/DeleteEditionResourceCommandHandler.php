<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\EditionResource\Delete;

use Colybri\Library\Domain\Service\EditionResource\EditionResourceDeleter;

final class DeleteEditionResourceCommandHandler
{
    public function __construct(private readonly EditionResourceDeleter $deleter)
    {
    }

    public function __invoke(DeleteEditionResourceCommand $command): void
    {
        $this->deleter->execute($command->resourceId(), $command->editionId());
    }
}
