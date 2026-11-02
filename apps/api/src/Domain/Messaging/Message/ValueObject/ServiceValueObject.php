<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Messaging\Message\ValueObject;

abstract class ServiceValueObject extends MessageValueObjectMultiton
{
    public static function instance(): static
    {
        return parent::instance();
    }

    abstract public function name(): string;
}
