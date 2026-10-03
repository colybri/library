<?php

declare(strict_types=1);

namespace Colybri\Library\Domain;

use Colybri\Library\Domain\Messaging\Message\ValueObject\ServiceValueObject;

class ServiceName extends ServiceValueObject
{
    private const NAME = 'library';

    public function name(): string
    {
        return self::NAME;
    }
}
