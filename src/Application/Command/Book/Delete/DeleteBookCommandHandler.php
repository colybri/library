<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Book\Delete;

use Colybri\Library\Domain\Messaging\EventBus;
use Colybri\Library\Domain\Service\Book\BookDeleter;

final class DeleteBookCommandHandler
{
    public function __construct(private BookDeleter $deleter, private EventBus $brokerBus)
    {
    }

    public function __invoke(DeleteBookCommand $cmd): void
    {
        $author = $this->deleter->execute(
            $cmd->bookId(),
        );

        foreach ($author->events() as $event) {
            $this->brokerBus->dispatch($event);
        }
    }
}
