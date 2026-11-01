<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query;

use Colybri\Library\Domain\Messaging\Message\SimpleMessage;

abstract class Query extends SimpleMessage
{
    final public static function messageType(): string
    {
        return 'query';
    }
}