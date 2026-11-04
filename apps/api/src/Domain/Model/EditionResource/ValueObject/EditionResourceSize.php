<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\EditionResource\ValueObject;

use Assert\Assert;
use PcComponentes\Ddd\Domain\Model\ValueObject\IntValueObject;

/** Size of the file in bytes */
final class EditionResourceSize extends IntValueObject
{
    /** 200 MiB, the same limit as nginx and PHP uploads */
    public const MAX_BYTES = 209715200;

    public static function from(int $value): static
    {
        Assert::that($value, sprintf('The file must be between 1 byte and %d bytes', self::MAX_BYTES), 'size')
            ->between(1, self::MAX_BYTES);

        return parent::from($value);
    }
}
