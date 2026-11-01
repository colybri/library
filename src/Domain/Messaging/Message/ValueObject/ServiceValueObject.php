<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Messaging\Message\ValueObject;

abstract class ServiceValueObject extends MessageValueObjectMultiton
{
    public static function instance(): ServiceValueObject
    {
        return parent::instance();
    }

    abstract public function name(): string;
}