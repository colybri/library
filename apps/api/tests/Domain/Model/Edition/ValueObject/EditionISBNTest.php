<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Domain\Model\Edition\ValueObject;

use Colybri\Library\Domain\Model\Edition\ValueObject\EditionISBN;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EditionISBNTest extends TestCase
{
    public static function isbns(): array
    {
        return [
            'ISBN-13' => ['9780306406157'],
            'ISBN-13 with hyphens' => ['978-0-306-40615-7'],
            'ISBN-10' => ['0306406152'],
            'ISBN-10 with hyphens' => ['0-306-40615-2'],
        ];
    }

    #[Test]
    #[DataProvider('isbns')]
    public function given_an_isbn_when_creating_then_keeps_the_full_isbn_13(string $isbn): void
    {
        self::assertSame(9780306406157, EditionISBN::from($isbn)->value());
    }
}
