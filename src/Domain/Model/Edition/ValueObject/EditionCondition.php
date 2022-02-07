<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Edition\ValueObject;

use Assert\Assert;
use Forkrefactor\Ddd\Domain\Model\ValueObject\StringValueObject;

class EditionCondition extends StringValueObject
{
    private const CONDITION_VALUES = ['new', 'second hand', 'inherited'];

    public static function from(string $value): static
    {
        Assert::that($value)->inArray(self::CONDITION_VALUES);

        return parent::from($value);
    }
}