<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Book\ValueObject;

use Forkrefactor\Ddd\Domain\Model\ValueObject\CollectionValueObject;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class BookAuthorIds extends CollectionValueObject
{
    public static function from(array $items): static
    {
        self::assert($items);

        return parent::from($items);
    }
    private static function assert(array $items)
    {
        foreach ($items as $elem) {
            if (false === \is_a($elem, Uuid::class)) {
                throw new \InvalidArgumentException(self::class . ' only accept '. Uuid::class);
            }
        }
    }
}