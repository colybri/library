<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Publisher\Delete;

use Colybri\Library\Domain\Service\Publisher\PublisherDeleter;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class DeletePublisherCommandHandler implements MessageHandlerInterface
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
