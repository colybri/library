<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Mock\Infrastructure\Bus;

use Assert\Assert;
use Colybri\Library\Domain\Messaging\DomainEvent;
use Colybri\Library\Domain\Messaging\EventBus;

class FakeEventBus implements EventBus
{
    private array $events;

    public function __construct()
    {
        $this->events = [];
    }

    public function dispatch(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->events[] = $event;

            Assert::lazy()
                ->that($event::messageVersion(), 'message_version')->greaterOrEqualThan(1)
                ->verifyNow();

            Assert::lazy()
                ->that($event::messageName(), 'message_name')->regex(
                    '/colybri\.library\.[0-9]+\.domain_event\.[\S]*\.[\S]*$/',
                )
                ->verifyNow();
        }
    }

    public function events(): array
    {
        return $this->events;
    }
}
