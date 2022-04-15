<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Country\ValueObject;

use Assert\Assert;
use Forkrefactor\Ddd\Domain\Model\ValueObject\StringValueObject;

class CountryAlpha2Code extends StringValueObject
{
    public static function from(string $value): static
    {
        Assert::that($value)->regex("/^[A-Z]{2}$/");
        return parent::from($value);
    }
}
