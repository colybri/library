<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Mock\Infrastructure\Bus;

use Assert\Assert;
use Forkrefactor\Ddd\Application\Command;
use Forkrefactor\Ddd\Domain\Model\DomainEvent;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class FakeMessageBus implements MessageBusInterface
{
    private array $events;

    public function __construct()
    {
        $this->events = [];
    }

    public function dispatch($event, array $stamps = []): Envelope
    {
        $this->events[] = $event;

        if (false === $event instanceof MockObject) {
            Assert::lazy()
                ->that(\get_parent_class($event), 'event')->inArray([DomainEvent::class, Command::class])
                ->verifyNow();

            Assert::lazy()
                ->that($event::messageVersion(), 'message_version')->greaterOrEqualThan(1)
                ->verifyNow();

            Assert::lazy()
                ->that($event::messageName(), 'message_name')->regex(
                    '/colybri\.library\.[0-9]+\.(domain_event|command).[\S]*\.[\S]*$/',
                )
                ->verifyNow();
        }

        return new Envelope($event);
    }

    public function events(): array
    {
        return $this->events;
    }
}