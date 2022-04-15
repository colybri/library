<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Author\Update;

use Colybri\Library\Domain\Service\Author\AuthorUpdater;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdateAuthorCommandHandler implements MessageHandlerInterface
{
    public function __construct(private AuthorUpdater $updater)
    {
    }

    public function __invoke(UpdateAuthorCommand $cmd): void
    {
        $this->updater->execute(
            $cmd->authorId(),
            $cmd->name(),
            $cmd->countryId(),
            $cmd->isPseudonymOf(),
            $cmd->bornAt(),
            $cmd->deathAt()
        );
    }
}
