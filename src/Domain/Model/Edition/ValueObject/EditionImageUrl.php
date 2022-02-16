<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Edition\ValueObject;

use Assert\Assert;
use Forkrefactor\Ddd\Domain\Model\ValueObject\StringValueObject;

class EditionImageUrl extends StringValueObject
{
    public static function from(string $value): static
    {
        Assert::that($value)->url();

        return parent::from($value);
    }
}