<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Messaging;

interface DomainEventSubscriber
{
    public function __invoke(DomainEvent $event): void;
    public static function subscribedTo(): array;
}