<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Messaging\Bus\Event;

use Colybri\Library\Domain\Messaging\DomainEvent;
use Colybri\Library\Domain\Messaging\DomainEventSubscriber;

class DomainEventMapping
{
    public function __construct(private iterable $mapping)
    {
        $this->mapping = $this->reindex($mapping);
    }
    public function eventFor(string $name): DomainEvent
    {
        if (!isset($this->mapping[$name])) {
            throw new \RuntimeException("The Domain Event Class for <$name> doesn't exists or have no subscribers");
        }
        return $this->mapping[$name]['event'];
    }
    public function subscribersFor(string $name): array
    {
        if (!isset($this->mapping[$name])) {
            throw new \RuntimeException("The Domain Event Class for <$name> doesn't exists or have no subscribers");
        }
        return $this->mapping[$name]['subscribers'];
    }
    private function reindex($mapping): array
    {
        $eventMap = [];
        /* @var $subscriber DomainEventSubscriber */
        foreach ($mapping as $subscriber) {
            /* @var $event DomainEvent */
            foreach ($subscriber::subscribedTo() as $event) {
                $eventMap[$event::messageName()]['event'] = $event;
                $eventMap[$event::messageName()]['subscribers'][] = $subscriber;
            }
        }
        return $eventMap;
    }
}