<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\EditionResource\ValueObject;

use Assert\Assert;
use PcComponentes\Ddd\Domain\Model\ValueObject\StringValueObject;

/** What the resource is, for example "Digital copy" or "Index" */
final class EditionResourceName extends StringValueObject
{
    public static function from(string $value): static
    {
        Assert::that($value, null, 'name')->notBlank()->maxLength(255);

        return parent::from($value);
    }
}
