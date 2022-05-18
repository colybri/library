<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Edition\Create;

use Colybri\Library\Domain\Service\Edition\EditionCreator;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class CreateEditionCommandHandler implements MessageHandlerInterface
{
    public function __construct(private EditionCreator $creator)
    {
    }

    public function __invoke(CreateEditionCommand $cmd): void
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
            null,
            $cmd->condition(),
            $cmd->pages(),
            $cmd->city(),
            $cmd->isOnLibrary()
        );
    }
}
