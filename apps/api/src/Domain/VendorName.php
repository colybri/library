<?php

declare(strict_types=1);

namespace Colybri\Library\Domain;

use Colybri\Library\Domain\Messaging\Message\ValueObject\VendorValueObject;

class VendorName extends VendorValueObject
{
    private const NAME = 'colybri';

    public function name(): string
    {
        return self::NAME;
    }
}
