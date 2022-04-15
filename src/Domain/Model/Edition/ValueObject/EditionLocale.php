<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Edition\ValueObject;

use Assert\Assert;
use Forkrefactor\Ddd\Domain\Model\ValueObject\StringValueObject;

final class EditionLocale extends StringValueObject
{
    private const LOCALES = ['es', 'en', 'fr', 'pt'];

    public static function from(string $value): static
    {
        Assert::that($value)->inArray(self::LOCALES);

        return parent::from($value);
    }
}
