<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Messaging\Message\ValueObject;

abstract class VendorValueObject extends MessageValueObjectMultiton
{
    public static function instance(): VendorValueObject
    {
        return parent::instance();
    }

    abstract public function name(): string;
}