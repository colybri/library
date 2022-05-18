<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Book\Create;

use Assert\Assert;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Book\Book;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIds;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIsPseudo;
use Colybri\Library\Domain\Model\Book\ValueObject\BookIsOnWishList;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYearIsEstimated;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYear;
use Colybri\Library\Domain\Model\Book\ValueObject\BookSubtitle;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionBookId;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionCity;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionCondition;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionGoogleBooksId;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionImageUrl;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionISBN;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionIsOnLibrary;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionLocale;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionPages;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionPublisherId;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionTitle;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionYear;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Application\Command;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\TopicGenerator\Topic;

final class CreateBookCommand extends Command
{
    protected const NAME = 'create';
    protected const VERSION = '1';

    public const BOOK_ID_PAYLOAD = 'id';
    public const BOOK_TITLE_PAYLOAD = 'title';
    public const BOOK_SUBTITLE_PAYLOAD = 'subtitle';
    public const BOOK_AUTHORS_PAYLOAD = 'authorsIds';
    public const BOOK_PUBLISH_YEAR_PAYLOAD = 'publishYear';
    public const BOOK_PUBLISH_YEAR_IS_ESTIMATED_PAYLOAD = 'estimatedPublishYear';
    public const BOOK_AUTHOR_IS_PSEUDO_PAYLOAD = 'isPseudo';
    public const BOOK_IS_ON_WISH_LIST_PAYLOAD = 'isOnWishList';

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

    public static function messageVersion(): string
    {
        return self::VERSION;
    }

    protected function assertPayload(): void
    {
        $payload = $this->messagePayload();

        Assert::lazy()
            ->that($payload, 'payload')->isArray()
            ->keyExists(self::BOOK_ID_PAYLOAD)
            ->keyExists(self::BOOK_TITLE_PAYLOAD)
            ->keyExists(self::BOOK_AUTHORS_PAYLOAD)
            ->keyExists(self::BOOK_PUBLISH_YEAR_PAYLOAD)
            ->keyExists(self::BOOK_PUBLISH_YEAR_IS_ESTIMATED_PAYLOAD)
            ->keyExists(self::BOOK_AUTHOR_IS_PSEUDO_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::BOOK_ID_PAYLOAD], self::BOOK_ID_PAYLOAD)->uuid()
            ->that($payload[self::BOOK_TITLE_PAYLOAD], self::BOOK_TITLE_PAYLOAD)->notEmpty()->string()
            ->that($payload[self::BOOK_SUBTITLE_PAYLOAD], self::BOOK_SUBTITLE_PAYLOAD)->nullOr()->string()
            ->that($payload[self::BOOK_AUTHORS_PAYLOAD], self::BOOK_AUTHORS_PAYLOAD)->isArray()->all()->uuid()
            ->that($payload[self::BOOK_PUBLISH_YEAR_PAYLOAD], self::BOOK_PUBLISH_YEAR_PAYLOAD)->notEmpty()->integer()
            ->that($payload[self::BOOK_PUBLISH_YEAR_IS_ESTIMATED_PAYLOAD], self::BOOK_PUBLISH_YEAR_IS_ESTIMATED_PAYLOAD)->boolean()
            ->that($payload[self::BOOK_AUTHOR_IS_PSEUDO_PAYLOAD], self::BOOK_AUTHOR_IS_PSEUDO_PAYLOAD)->boolean()
            ->that($payload[self::BOOK_IS_ON_WISH_LIST_PAYLOAD], self::BOOK_IS_ON_WISH_LIST_PAYLOAD)->nullOr()->boolean()
            ->verifyNow();

        $this->bookId = Uuid::from((string)$payload[self::BOOK_ID_PAYLOAD]);
        $this->title = BookTitle::from((string)$payload[self::BOOK_TITLE_PAYLOAD]);
        $this->subtitle = BookSubtitle::from((string)$payload[self::BOOK_SUBTITLE_PAYLOAD]);
        $this->authorIds = BookAuthorIds::from(array_map(fn($id) => Uuid::from((string)$id), $payload[self::BOOK_AUTHORS_PAYLOAD]));
        $this->publishYear = BookPublishYear::from((int)$payload[self::BOOK_PUBLISH_YEAR_PAYLOAD]);
        $this->publishYearIsEstimated = BookPublishYearIsEstimated::from((bool)$payload[self::BOOK_PUBLISH_YEAR_IS_ESTIMATED_PAYLOAD]);
        $this->isPseudo = BookAuthorIsPseudo::from((bool)$payload[self::BOOK_AUTHOR_IS_PSEUDO_PAYLOAD]);
        $this->isOnWishList = BookIsOnWishList::from((bool)$payload[self::BOOK_IS_ON_WISH_LIST_PAYLOAD]);
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
