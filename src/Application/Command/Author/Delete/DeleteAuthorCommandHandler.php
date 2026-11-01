<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Author\Delete;

use Colybri\Library\Domain\Messaging\EventBus;
use Colybri\Library\Domain\Service\Author\AuthorDeleter;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class DeleteAuthorCommandHandler implements MessageHandlerInterface
{
    public function __construct(private AuthorDeleter $deleter, private EventBus $brokerBus)
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
