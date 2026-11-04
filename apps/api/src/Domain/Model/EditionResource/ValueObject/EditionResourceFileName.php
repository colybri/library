<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\EditionResource\ValueObject;

use Assert\Assert;
use PcComponentes\Ddd\Domain\Model\ValueObject\StringValueObject;

/** Name of the file as it was uploaded, used when it is downloaded */
final class EditionResourceFileName extends StringValueObject
{
    public static function from(string $value): static
    {
        Assert::that($value, null, 'fileName')->notBlank()->maxLength(255);
        Assert::that($value, 'The file name cannot contain a path', 'fileName')->notContains('/')->notContains('\\');

        return parent::from($value);
    }
}
