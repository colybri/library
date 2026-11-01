<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\User\ValueObject;

use Assert\Assert;
use Forkrefactor\Ddd\Domain\Model\ValueObject\StringValueObject;

class UserEmail extends StringValueObject
{
    public static function from(string $value): static
    {
        Assert::that($value)->email($value);
        return parent::from($value);
    }
}