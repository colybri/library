<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Book\Update;

use Colybri\Library\Domain\Service\Book\BookUpdater;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdateBookCommandHandler implements MessageHandlerInterface
{
    public function __construct(private BookUpdater $updater)
    {
    }

    public function __invoke(UpdateBookCommand $cmd): void
    {
        $this->updater->execute(
            $cmd->bookId(),
            $cmd->title(),
            $cmd->subtitle(),
            $cmd->authorIds(),
            $cmd->isPseudo(),
            $cmd->publishYear(),
            $cmd->publishYearIsEstimated(),
            $cmd->isOnWishList()
        );
    }
}
