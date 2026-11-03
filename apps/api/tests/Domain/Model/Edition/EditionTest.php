<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Domain\Model\Edition;

use Colybri\Library\Domain\Model\Edition\ValueObject\EditionImageUrl;
use Colybri\Library\Tests\Mock\Domain\Model\Edition\EditionObjectMother;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EditionTest extends TestCase
{
    #[Test]
    public function given_an_edition_with_image_when_serialized_then_image_is_included(): void
    {
        $edition = (new EditionObjectMother(image: EditionImageUrl::from('https://example.org/cover.jpg')))->create();

        $json = json_decode((string) json_encode($edition), true);

        self::assertSame('https://example.org/cover.jpg', $json['image']);
        self::assertSame(
            ['id', 'title', 'subtitle', 'year', 'pages', 'language', 'googleId', 'isbn', 'city', 'image', 'publisherId', 'bookId', 'isOnLibrary', 'condition'],
            array_keys($json)
        );
    }
}
