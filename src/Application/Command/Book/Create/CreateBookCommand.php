<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Book\Create;

use Assert\Assert;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Book\Book;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIds;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIsPseudo;
use Colybri\Library\Domain\Model\Book\ValueObject\BookHaveEstimatedPublishYear;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYear;
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
    public const BOOK_AUTHORS_PAYLOAD = 'authorsIds';
    public const BOOK_PUBLISH_YEAR_PAYLOAD = 'publishYear';
    public const BOOK_IS_ESTIMATED_PUBLISH = 'estimatedPublishYear';
    public const BOOK_PUBLISHER_ID_PAYLOAD = 'publisherId';
    public const BOOK_IS_PSEUDO_PAYLOAD = 'isPseudo';

    public const EDITION_GOOGLE_ID_PAYLOAD = 'googleId';
    public const EDITION_ISBN_PAYLOAD = 'isbn';
    public const EDITION_YEAR_PAYLOAD = 'editionYear';
    public const EDITION_TITLE_PAYLOAD = 'editionTitle';
    public const EDITION_LANGUAGE_PAYLOAD = 'language';
    public const EDITION_IMAGE_PAYLOAD = 'image';
    public const EDITION_IS_ON_LIBRARY = 'isOnLibrary';
    public const EDITION_RESOURCES_PAYLOAD = 'resource';
    public const EDITION_CONDITION_PAYLOAD = 'condition';
    public const EDITION_PAGES_PAYLOAD = 'pages';
    public const EDITION_CITY_PAYLOAD = 'city';

    private Uuid $bookId;
    private BookTitle $bookTitle;
    private BookAuthorIds $authorIds;
    private BookPublishYear $publishYear;
    private BookHaveEstimatedPublishYear $bookHaveEstimatedPublishYear;
    private BookAuthorIsPseudo $isPseudo;

    private EditionYear $editionYear;
    private EditionPublisherId $publisherId;
    private ?EditionGoogleBooksId $googleBooksId;
    private EditionBookId $editionBookId;
    private EditionISBN $editionISBN;
    private EditionIsOnLibrary $editionIsOnLibrary;
    private EditionTitle $editionTitle;
    private EditionLocale $editionLocale;
    private ?EditionImageUrl $editionImageUrl;
    private EditionCondition $editionCondition;
    private EditionCity $editionCity;
    private EditionPages $editionPages;

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
            ->keyExists(self::BOOK_IS_ESTIMATED_PUBLISH)
            ->keyExists(self::BOOK_PUBLISHER_ID_PAYLOAD)
            ->keyExists(self::BOOK_IS_PSEUDO_PAYLOAD)
            ->keyExists(self::EDITION_YEAR_PAYLOAD)
            ->keyExists(self::EDITION_GOOGLE_ID_PAYLOAD)
            ->keyExists(self::EDITION_ISBN_PAYLOAD)
            ->keyExists(self::EDITION_TITLE_PAYLOAD)
            ->keyExists(self::EDITION_IS_ON_LIBRARY)
            ->keyExists(self::EDITION_LANGUAGE_PAYLOAD)
            ->keyExists(self::EDITION_IMAGE_PAYLOAD)
            ->keyExists(self::EDITION_RESOURCES_PAYLOAD)
            ->keyExists(self::EDITION_CONDITION_PAYLOAD)
            ->keyExists(self::EDITION_PAGES_PAYLOAD)
            ->keyExists(self::EDITION_CITY_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::BOOK_ID_PAYLOAD], self::BOOK_ID_PAYLOAD)->uuid()
            ->that($payload[self::BOOK_TITLE_PAYLOAD], self::BOOK_TITLE_PAYLOAD)->notEmpty()->string()
            ->that($payload[self::BOOK_AUTHORS_PAYLOAD], self::BOOK_AUTHORS_PAYLOAD)->isArray()->all()->uuid()
            ->that($payload[self::BOOK_PUBLISH_YEAR_PAYLOAD], self::BOOK_PUBLISH_YEAR_PAYLOAD)->notEmpty()->integer()
            ->that($payload[self::BOOK_IS_ESTIMATED_PUBLISH], self::BOOK_IS_ESTIMATED_PUBLISH)->boolean()
            ->that($payload[self::BOOK_PUBLISHER_ID_PAYLOAD], self::BOOK_PUBLISHER_ID_PAYLOAD)->notEmpty()->uuid()
            ->that($payload[self::BOOK_IS_PSEUDO_PAYLOAD], self::BOOK_IS_PSEUDO_PAYLOAD)->boolean()
            ->that($payload[self::EDITION_YEAR_PAYLOAD], self::EDITION_YEAR_PAYLOAD)->notEmpty()->integer()
            ->that($payload[self::EDITION_GOOGLE_ID_PAYLOAD], self::EDITION_GOOGLE_ID_PAYLOAD)->nullOr()->string()
            ->that($payload[self::EDITION_ISBN_PAYLOAD], self::EDITION_ISBN_PAYLOAD)->notEmpty()->regex(
                '/\b(?:ISBN(?:: ?| ))?((?:97[89])?\d{9}[\dx])\b/i'
            )
            ->that($payload[self::EDITION_IS_ON_LIBRARY], self::EDITION_IS_ON_LIBRARY)->boolean()
            ->that($payload[self::EDITION_TITLE_PAYLOAD], self::EDITION_TITLE_PAYLOAD)->notEmpty()->string()
            ->that($payload[self::EDITION_LANGUAGE_PAYLOAD], self::EDITION_LANGUAGE_PAYLOAD)->notEmpty()->string()
            ->that($payload[self::EDITION_IMAGE_PAYLOAD], self::EDITION_IMAGE_PAYLOAD)->nullOr()->url()
            ->that($payload[self::EDITION_RESOURCES_PAYLOAD], self::EDITION_RESOURCES_PAYLOAD)->nullOr()->file()
            ->that($payload[self::EDITION_CONDITION_PAYLOAD], self::EDITION_CONDITION_PAYLOAD)->nullOr()->string()
            ->that($payload[self::EDITION_PAGES_PAYLOAD], self::EDITION_PAGES_PAYLOAD)->nullOr()->integer()
            ->that($payload[self::EDITION_CITY_PAYLOAD], self::BOOK_IS_PSEUDO_PAYLOAD)->notEmpty()->string()
            ->verifyNow();

        $this->bookId = Uuid::from((string)$payload[self::BOOK_ID_PAYLOAD]);
        $this->bookTitle = BookTitle::from((string)$payload[self::BOOK_TITLE_PAYLOAD]);
        $this->authorIds = BookAuthorIds::from(array_map(fn($id) => Uuid::from((string)$id), $payload[self::BOOK_AUTHORS_PAYLOAD]));
        $this->publishYear = BookPublishYear::from((int)$payload[self::BOOK_PUBLISH_YEAR_PAYLOAD]);
        $this->bookHaveEstimatedPublishYear = BookHaveEstimatedPublishYear::from((bool)$payload[self::BOOK_IS_ESTIMATED_PUBLISH]);
        $this->isPseudo = BookAuthorIsPseudo::from((bool)$payload[self::BOOK_IS_PSEUDO_PAYLOAD]);

        $this->editionISBN = EditionISBN::from((int)$payload[self::EDITION_ISBN_PAYLOAD]);
        $this->googleBooksId = null === $payload[self::EDITION_GOOGLE_ID_PAYLOAD] ? null : EditionGoogleBooksId::from((string)$payload[self::EDITION_GOOGLE_ID_PAYLOAD]);
        $this->editionImageUrl = null === $payload[self::EDITION_IMAGE_PAYLOAD] ? null : EditionImageUrl::from((string)$payload[self::EDITION_IMAGE_PAYLOAD]);


    }
}