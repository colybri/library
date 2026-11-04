<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\EditionResource\ValueObject;

use Assert\Assert;
use PcComponentes\Ddd\Domain\Model\ValueObject\StringValueObject;

/** Media type detected from the content of the file */
final class EditionResourceMediaType extends StringValueObject
{
    public const ALLOWED = [
        'application/pdf',
        'application/epub+zip',
        'image/jpeg',
        'image/png',
        'image/webp',
        'text/plain',
    ];

    public static function from(string $value): static
    {
        Assert::that($value, sprintf('Files of type "%s" are not accepted. Accepted types: %s', $value, implode(', ', self::ALLOWED)), 'mediaType')
            ->inArray(self::ALLOWED);

        return parent::from($value);
    }
}
