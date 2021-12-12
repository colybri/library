<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Author\Update;

use Colybri\Library\Domain\Service\Author\AuthorUpdater;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;


class UpdateAuthorCommandHandler implements MessageHandlerInterface
{
    public function __construct(private AuthorUpdater $creator)
    {
    }

    public function __invoke(UpdateAuthorCommand $cmd)
    {
        $author = $this->creator->execute(
            $cmd->authorId(),
            $cmd->firstName(),
            $cmd->lastName(),
            $cmd->countryId(),
            $cmd->isPseudonymOf(),
            $cmd->bornAt(),
            $cmd->deathAt()
        );

    }
}