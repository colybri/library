<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Author\Create;

use Colybri\Library\Domain\Messaging\EventBus;
use Colybri\Library\Domain\Service\Author\AuthorCreator;

final class CreateAuthorCommandHandler
{
    public function __construct(private AuthorCreator $creator, private EventBus $brokerBus)
    {
    }

    public function __invoke(CreateAuthorCommand $cmd): void
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

        foreach ($author->events() as $event) {
            $this->brokerBus->dispatch($event);
        }
    }
}
