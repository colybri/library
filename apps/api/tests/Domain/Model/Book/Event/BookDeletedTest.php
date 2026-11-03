<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Domain\Model\Book\Event;

use Colybri\Library\Domain\Model\Author\Event\AuthorDeleted;
use Colybri\Library\Domain\Model\Book\Event\BookDeleted;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BookDeletedTest extends TestCase
{
    #[Test]
    public function given_book_deleted_when_name_is_asked_then_it_is_the_book_routing_key(): void
    {
        self::assertSame('colybri.library.1.domain_event.book.deleted', BookDeleted::messageName());
        self::assertNotSame(AuthorDeleted::messageName(), BookDeleted::messageName());
    }

    #[Test]
    public function given_book_id_when_event_is_created_then_payload_carries_it(): void
    {
        $bookId = Uuid::v4();

        $event = BookDeleted::from($bookId);

        self::assertTrue($bookId->equalTo($event->bookId()));
        self::assertSame(['id' => $bookId->value()], $event->messagePayload());
    }
}
