<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Messaging;

interface EventBus
{
    public function dispatch(DomainEvent... $events): void;
}