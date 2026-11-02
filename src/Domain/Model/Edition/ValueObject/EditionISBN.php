<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Edition\ValueObject;

use PcComponentes\Ddd\Domain\Model\ValueObject\IntValueObject;
use Isbn\Isbn;

final class EditionISBN extends IntValueObject
{
    public static function from($value): static
    {
        $validator = new Isbn();
        $isbn = $validator->hyphens->removeHyphens((string)$value);

        // Translate::to13() only understands ISBN-10: given an ISBN-13 it
        // returns "978-<12 digits><check>", which the int cast cut to 978.
        if (13 !== \strlen($isbn)) {
            $isbn = $validator->hyphens->removeHyphens($validator->translate->to13($isbn));
        }

        return parent::from((int)$isbn);
    }
}
