<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Messaging;

use Colybri\Library\Domain\Messaging\Message\AggregateMessage;

abstract class DomainEvent extends AggregateMessage
{
    final public static function messageType(): string
    {
        return 'domain_event';
    }
}