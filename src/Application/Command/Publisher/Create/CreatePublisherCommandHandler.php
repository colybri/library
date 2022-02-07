<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Publisher\Create;

use Colybri\Library\Domain\Service\Publisher\PublisherCreator;

class CreatePublisherCommandHandler
{

    public function __construct(private PublisherCreator $creator)
    {
    }

    public function __invoke(CreatePublisherCommand $command): void
    {
        $this->creator->execute($command->publisherId(), $command->name(), $command->city(), $command->countryId(), $command->foundation());
    }
}