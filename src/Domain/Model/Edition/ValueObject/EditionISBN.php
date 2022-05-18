<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Edition\ValueObject;

use Forkrefactor\Ddd\Domain\Model\ValueObject\IntValueObject;
use Isbn\Isbn;

final class EditionISBN extends IntValueObject
{
    public static function from($value): static
    {
        $validator = new Isbn();
        $isbn = $validator->hyphens->removeHyphens((string)$value);
        return parent::from((int)$validator->translate->to13($isbn));
    }
}
