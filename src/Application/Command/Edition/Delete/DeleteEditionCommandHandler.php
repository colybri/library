<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Edition\Delete;

use Colybri\Library\Domain\Service\Edition\EditionDeleter;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class DeleteEditionCommandHandler implements MessageHandlerInterface
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
