<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Book\Event;

use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Book\Book;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIds;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIsPseudo;
use Colybri\Library\Domain\Model\Book\ValueObject\BookIsOnWishList;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYear;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYearIsEstimated;
use Colybri\Library\Domain\Model\Book\ValueObject\BookSubtitle;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Domain\Model\DomainEvent;
use Forkrefactor\Ddd\Domain\Model\ValueObject\DateTimeValueObject;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\TopicGenerator\Topic;

final class BookCreated extends DomainEvent
{
    public const BOOK_ID_PAYLOAD = 'id';
    public const BOOK_TITLE_PAYLOAD = 'title';
    public const BOOK_SUBTITLE_PAYLOAD = 'subtitle';
    public const BOOK_AUTHORS_PAYLOAD = 'authorsIds';
    public const BOOK_PUBLISH_YEAR_PAYLOAD = 'publishYear';
    public const BOOK_PUBLISH_YEAR_IS_ESTIMATED = 'estimatedPublishYear';
    public const BOOK_IS_PSEUDO_PAYLOAD = 'isPseudo';
    public const BOOK_IS_ON_WISH_LIST = 'isOnWishList';

    private const NAME = 'created';
    private const VERSION = '1';

    private Uuid $bookId;
    private BookTitle $title;
    private BookSubtitle $subtitle;
    private BookAuthorIds $authorIds;
    private BookPublishYear $publishYear;
    private BookPublishYearIsEstimated $publishYearIsEstimated;
    private BookAuthorIsPseudo $isPseudo;
    private BookIsOnWishList $isOnWishList;

    public static function messageName(): string
    {
        return Topic::generate(
            CompanyName::instance(),
            ServiceName::instance(),
            self::messageVersion(),
            self::messageType(),
            Book::modelName(),
            self::NAME
        );
    }

    public static function from(
        Uuid $id,
        BookTitle $title,
        ?BookSubtitle $subtitle,
        BookAuthorIds $authorIds,
        BookAuthorIsPseudo $isPseudo,
        BookPublishYear $publishYear,
        BookPublishYearIsEstimated $publishYearIsEstimated,
        BookIsOnWishList $isOnWishList
    ): static {
        return static::fromPayload(
            Uuid::v4(),
            $id,
            new DateTimeValueObject(),
            [
                self::BOOK_ID_PAYLOAD => $id->value(),
                self::BOOK_TITLE_PAYLOAD => $title->value(),
                self::BOOK_SUBTITLE_PAYLOAD => $subtitle?->value(),
                self::BOOK_AUTHORS_PAYLOAD => json_encode($authorIds),
                self::BOOK_PUBLISH_YEAR_PAYLOAD => $publishYear->value(),
                self::BOOK_PUBLISH_YEAR_IS_ESTIMATED => $publishYearIsEstimated->value(),
                self::BOOK_IS_PSEUDO_PAYLOAD => $isPseudo->value(),
                self::BOOK_IS_ON_WISH_LIST => $isOnWishList->value(),

            ]
        );
    }

    protected function assertPayload(): void
    {
        $payload = $this->messagePayload();

        $this->bookId = Uuid::from((string)$payload[self::BOOK_ID_PAYLOAD]);
        $this->title = BookTitle::from((string)$payload[self::BOOK_TITLE_PAYLOAD]);
        $this->subtitle = BookSubtitle::from((string)$payload[self::BOOK_SUBTITLE_PAYLOAD]);
        $this->authorIds = BookAuthorIds::from(array_map(fn($id) => Uuid::from((string)$id), json_decode($payload[self::BOOK_AUTHORS_PAYLOAD])));
        $this->isPseudo = BookAuthorIsPseudo::from((bool)$payload[self::BOOK_IS_PSEUDO_PAYLOAD]);
        $this->publishYear = BookPublishYear::from((int)$payload[self::BOOK_PUBLISH_YEAR_PAYLOAD]);
        $this->publishYearIsEstimated = BookPublishYearIsEstimated::from((bool)$payload[self::BOOK_PUBLISH_YEAR_IS_ESTIMATED]);
        $this->isOnWishList = BookIsOnWishList::from((bool)$payload[self::BOOK_IS_ON_WISH_LIST]);
    }

    public static function messageVersion(): string
    {
        return self::VERSION;
    }

    public function bookId(): Uuid
    {
        return $this->bookId;
    }

    public function title(): BookTitle
    {
        return $this->title;
    }

    public function subtitle(): ?BookSubtitle
    {
        return $this->subtitle;
    }

    public function authorIds(): BookAuthorIds
    {
        return $this->authorIds;
    }

    public function publishYear(): BookPublishYear
    {
        return $this->publishYear;
    }

    public function publishYearIsEstimated(): BookPublishYearIsEstimated
    {
        return $this->publishYearIsEstimated;
    }

    public function isPseudo(): BookAuthorIsPseudo
    {
        return $this->isPseudo;
    }

    public function isOnWishList(): BookIsOnWishList
    {
        return $this->isOnWishList;
    }
}
