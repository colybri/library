<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Publisher\Delete;

use Colybri\Library\Domain\Service\Publisher\PublisherDeleter;

final class DeletePublisherCommandHandler
{
    public function __construct(private PublisherDeleter $deleter)
    {
    }

    public function __invoke(DeletePublisherCommand $cmd): void
    {
        $author = $this->deleter->execute(
            $cmd->publisherId(),
        );
    }
}
