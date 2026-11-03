<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Domain\Model\Book\Event;

use Colybri\Library\Domain\Model\Book\Event\BookCreated;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIds;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIsPseudo;
use Colybri\Library\Domain\Model\Book\ValueObject\BookIsOnWishList;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYear;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYearIsEstimated;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use PcComponentes\Ddd\Domain\Model\ValueObject\DateTimeValueObject;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BookCreatedTest extends TestCase
{
    private const AUTHOR_ID = '19424912-a0fc-4a94-869e-5b02d9965aa9';

    #[Test]
    public function given_a_book_when_event_is_created_then_authors_ids_is_a_list_of_uuids(): void
    {
        $event = BookCreated::from(
            Uuid::v4(),
            BookTitle::from('Major Trends in Jewish Mysticism'),
            null,
            BookAuthorIds::from([Uuid::from(self::AUTHOR_ID)]),
            BookAuthorIsPseudo::from(false),
            BookPublishYear::from(1941),
            BookPublishYearIsEstimated::from(false),
            BookIsOnWishList::from(false)
        );

        self::assertSame([self::AUTHOR_ID], $event->messagePayload()[BookCreated::BOOK_AUTHORS_PAYLOAD]);
        self::assertTrue(Uuid::from(self::AUTHOR_ID)->equalTo($event->authorIds()->items()[0]));
    }

    #[Test]
    public function given_a_payload_from_before_the_fix_when_event_is_rebuilt_then_authors_ids_are_decoded(): void
    {
        $event = BookCreated::fromPayload(
            Uuid::v4(),
            Uuid::v4(),
            DateTimeValueObject::now(),
            [
                BookCreated::BOOK_ID_PAYLOAD => Uuid::v4()->value(),
                BookCreated::BOOK_TITLE_PAYLOAD => 'Major Trends in Jewish Mysticism',
                BookCreated::BOOK_SUBTITLE_PAYLOAD => null,
                BookCreated::BOOK_AUTHORS_PAYLOAD => json_encode([self::AUTHOR_ID]),
                BookCreated::BOOK_PUBLISH_YEAR_PAYLOAD => 1941,
                BookCreated::BOOK_PUBLISH_YEAR_IS_ESTIMATED => false,
                BookCreated::BOOK_IS_PSEUDO_PAYLOAD => false,
                BookCreated::BOOK_IS_ON_WISH_LIST => false,
            ]
        );

        self::assertTrue(Uuid::from(self::AUTHOR_ID)->equalTo($event->authorIds()->items()[0]));
    }
}
