<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Edition\Update;

use Colybri\Library\Domain\Service\Edition\EditionUpdater;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdateEditionHandler implements MessageHandlerInterface
{
    public function __construct(private EditionUpdater $creator)
    {
    }

    public function __invoke(UpdateEditionCommand $cmd): void
    {
        $edition = $this->creator->execute(
            $cmd->editionId(),
            $cmd->year(),
            $cmd->publisherId(),
            $cmd->bookId(),
            $cmd->googleBooksId(),
            $cmd->isbn(),
            $cmd->title(),
            $cmd->subtitle(),
            $cmd->locale(),
            $cmd->image(),
            $cmd->pages(),
            $cmd->city(),
            $cmd->isOnLibrary(),
            $cmd->condition()
        );
    }
}
