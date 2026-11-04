<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\EditionResource\Create;

use Colybri\Library\Domain\Service\EditionResource\EditionResourceCreator;

final class CreateEditionResourceCommandHandler
{
    public function __construct(private readonly EditionResourceCreator $creator)
    {
    }

    public function __invoke(CreateEditionResourceCommand $command): void
    {
        $this->creator->execute(
            $command->resourceId(),
            $command->editionId(),
            $command->name(),
            $command->fileName(),
            $command->mediaType(),
            $command->size(),
            $command->path(),
        );
    }
}
