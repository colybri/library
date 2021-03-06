<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Book\Delete;

use Colybri\Library\Domain\Service\Book\BookDeleter;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class DeleteBookCommandHandler implements MessageHandlerInterface
{
    public function __construct(private BookDeleter $deleter, private MessageBusInterface $brokerBus)
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
