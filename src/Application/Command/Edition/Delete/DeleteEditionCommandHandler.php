<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Edition\Delete;

use Colybri\Library\Domain\Service\Edition\EditionDeleter;

final class DeleteEditionCommandHandler
{
    public function __construct(private EditionDeleter $deleter)
    {
    }

    public function __invoke(DeleteEditionCommand $cmd): void
    {
        $this->deleter->execute(
            $cmd->editionId(),
        );
    }
}
