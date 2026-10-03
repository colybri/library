<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command;

use Colybri\Library\Domain\Messaging\Message\SimpleMessage;

abstract class Command extends SimpleMessage
{
    final public static function messageType(): string
    {
        return 'command';
    }
}