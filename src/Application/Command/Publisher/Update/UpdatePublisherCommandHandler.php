<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Publisher\Update;

use Colybri\Library\Domain\Service\Publisher\PublisherUpdater;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdatePublisherCommandHandler implements MessageHandlerInterface
{
    public function __construct(private PublisherUpdater $updater)
    {
    }

    public function __invoke(UpdatePublisherCommand $cmd): void
    {
        $this->updater->execute(
            $cmd->publisherId(),
            $cmd->name(),
            $cmd->city(),
            $cmd->countryId(),
            $cmd->foundation()
        );
    }
}
