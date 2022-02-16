<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Edition\ValueObject;

use Forkrefactor\Ddd\Domain\Model\ValueObject\IntValueObject;

final class EditionISBN extends IntValueObject
{
    public static function from(int $value): static
    {
        return parent::from($value);
    }
}