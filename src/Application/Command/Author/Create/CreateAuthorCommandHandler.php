<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Author\Create;

use Colybri\Library\Domain\Service\Author\AuthorCreator;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class CreateAuthorCommandHandler implements MessageHandlerInterface
{

    public function __construct(private AuthorCreator $creator, private MessageBusInterface $brokerBus)
    {
    }

    public function __invoke(CreateAuthorCommand $cmd): void
    {
        $author = $this->creator->execute(
            $cmd->authorId(),
            $cmd->name(),
            $cmd->countryId(),
            $cmd->isPseudonymOf(),
            $cmd->bornAt(),
            $cmd->deathAt()
        );

        foreach ($author->events() as $event) {
            $this->brokerBus->dispatch($event);
        }
    }
}