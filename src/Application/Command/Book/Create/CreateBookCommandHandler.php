<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Book\Create;

use Colybri\Library\Domain\Service\Book\BookCreator;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class CreateBookCommandHandler implements MessageHandlerInterface
{

    public function __construct(private BookCreator $creator, private MessageBusInterface $brokerBus)
    {
    }

    public function __invoke(CreateBookCommand $cmd): void
    {
        dd($cmd);
        $book = $this->creator->execute(

        );
        foreach ($book->events() as $event) {
            $this->brokerBus->dispatch($event);
        }
    }
}