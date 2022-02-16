<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Author\Delete;

use Colybri\Library\Domain\Service\Author\AuthorDeleter;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class DeleteAuthorCommandHandler implements MessageHandlerInterface
{
    public function __construct(private AuthorDeleter $deleter, private MessageBusInterface $brokerBus)
    {
    }

    public function __invoke(DeleteAuthorCommand $cmd): void
    {
        $author = $this->deleter->execute(
            $cmd->authorId(),
        );

        foreach ($author->events() as $event) {
            $this->brokerBus->dispatch($event);
        }
    }
}