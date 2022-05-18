<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Edition\Create;

use Assert\Assert;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Book\Book;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionCity;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionCondition;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionGoogleBooksId;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionImageUrl;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionISBN;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionIsOnLibrary;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionLocale;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionPages;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionSubtitle;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionTitle;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionYear;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Application\Command;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Isbn\Isbn;
use PcComponentes\TopicGenerator\Topic;

final class CreateEditionCommand extends Command
{
    protected const NAME = 'create';
    protected const VERSION = '1';

    public const EDITION_ID_PAYLOAD = 'id';
    public const EDITION_YEAR_PAYLOAD = 'year';
    public const EDITION_PUBLISHER_ID_PAYLOAD = 'publisherId';
    public const EDITION_BOOK_ID_PAYLOAD = 'bookId';
    public const EDITION_GOOGLE_ID_PAYLOAD = 'googleId';
    public const EDITION_ISBN_PAYLOAD = 'isbn';
    public const EDITION_TITLE_PAYLOAD = 'title';
    public const EDITION_SUBTITLE_PAYLOAD = 'subtitle';
    public const EDITION_LANGUAGE_PAYLOAD = 'language';
    public const EDITION_IMAGE_PAYLOAD = 'image';
    public const EDITION_IS_ON_LIBRARY = 'isOnLibrary';
    public const EDITION_RESOURCES_PAYLOAD = 'resources';
    public const EDITION_CONDITION_PAYLOAD = 'condition';
    public const EDITION_PAGES_PAYLOAD = 'pages';
    public const EDITION_CITY_PAYLOAD = 'city';

    private Uuid $editionId;
    private EditionYear $year;
    private Uuid $publisherId;
    private Uuid $bookId;
    private ?EditionGoogleBooksId $googleBooksId;
    private EditionISBN $isbn;
    private EditionTitle $title;
    private ?EditionSubtitle $subtitle;
    private EditionLocale $locale;
    private ?EditionImageUrl $imageUrl;
    private $resource;
    private ?EditionCondition $condition;
    private EditionCity $city;
    private ?EditionPages $pages;
    private EditionIsOnLibrary $isOnLibrary;

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


        dd($payload);

        Assert::lazy()
            ->that($payload, 'payload')->isArray()
            ->keyExists(self::EDITION_ID_PAYLOAD)
            ->keyExists(self::EDITION_YEAR_PAYLOAD)
            ->keyExists(self::EDITION_GOOGLE_ID_PAYLOAD)
            ->keyExists(self::EDITION_ISBN_PAYLOAD)
            ->keyExists(self::EDITION_PUBLISHER_ID_PAYLOAD)
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
            ->that($payload[self::EDITION_ID_PAYLOAD], self::EDITION_ID_PAYLOAD)->uuid()
            ->that($payload[self::EDITION_YEAR_PAYLOAD], self::EDITION_YEAR_PAYLOAD)->notEmpty()->integer()
            ->that($payload[self::EDITION_PUBLISHER_ID_PAYLOAD], self::EDITION_PUBLISHER_ID_PAYLOAD)->notEmpty()->uuid()
            ->that($payload[self::EDITION_BOOK_ID_PAYLOAD], self::EDITION_BOOK_ID_PAYLOAD)->notEmpty()->uuid()
            ->that($payload[self::EDITION_GOOGLE_ID_PAYLOAD], self::EDITION_GOOGLE_ID_PAYLOAD)->nullOr()->string()
            ->that($payload[self::EDITION_ISBN_PAYLOAD], self::EDITION_ISBN_PAYLOAD)->notEmpty()->string()
            ->that((new Isbn())->validation->isbn($payload[self::EDITION_ISBN_PAYLOAD]))->true()
            ->that($payload[self::EDITION_TITLE_PAYLOAD], self::EDITION_TITLE_PAYLOAD)->notEmpty()->string()
            ->that($payload[self::EDITION_SUBTITLE_PAYLOAD], self::EDITION_SUBTITLE_PAYLOAD)->nullOr()->string()
            ->that($payload[self::EDITION_LANGUAGE_PAYLOAD], self::EDITION_LANGUAGE_PAYLOAD)->notEmpty()->string()
            ->that($payload[self::EDITION_IMAGE_PAYLOAD], self::EDITION_IMAGE_PAYLOAD)->nullOr()->string()
            ->that($payload[self::EDITION_RESOURCES_PAYLOAD], self::EDITION_RESOURCES_PAYLOAD)->nullOr()->file()
            ->that($payload[self::EDITION_CONDITION_PAYLOAD], self::EDITION_CONDITION_PAYLOAD)->nullOr()->string()
            ->that($payload[self::EDITION_PAGES_PAYLOAD], self::EDITION_PAGES_PAYLOAD)->nullOr()->integer()
            ->that($payload[self::EDITION_CITY_PAYLOAD], self::EDITION_CITY_PAYLOAD)->notEmpty()->string()
            ->that($payload[self::EDITION_IS_ON_LIBRARY], self::EDITION_IS_ON_LIBRARY)->boolean()
            ->verifyNow();

        if ($payload[self::EDITION_IS_ON_LIBRARY] === true) {
            Assert::that($payload[self::EDITION_CONDITION_PAYLOAD])->string();
        }

        if ($payload[self::EDITION_IS_ON_LIBRARY] === false) {
            Assert::that($payload[self::EDITION_CONDITION_PAYLOAD])->null();
        }

        $this->editionId = Uuid::from((string)$payload[self::EDITION_ID_PAYLOAD]);
        $this->year = EditionYear::from($payload[self::EDITION_YEAR_PAYLOAD]);
        $this->publisherId = Uuid::from((string)$payload[self::EDITION_PUBLISHER_ID_PAYLOAD]);
        $this->bookId = Uuid::from((string)$payload[self::EDITION_BOOK_ID_PAYLOAD]);
        $this->googleBooksId = null === $payload[self::EDITION_GOOGLE_ID_PAYLOAD] ? null : EditionGoogleBooksId::from((string)$payload[self::EDITION_GOOGLE_ID_PAYLOAD]);
        $this->isbn = EditionISBN::from($payload[self::EDITION_ISBN_PAYLOAD]);
        $this->title =  EditionTitle::from((string)$payload[self::EDITION_TITLE_PAYLOAD]);
        $this->subtitle = null === $payload[self::EDITION_SUBTITLE_PAYLOAD] ? null : EditionSubtitle::from((string)$payload[self::EDITION_SUBTITLE_PAYLOAD]);
        $this->locale = EditionLocale::from((string)$payload[self::EDITION_LANGUAGE_PAYLOAD]);
        $this->imageUrl = null === $payload[self::EDITION_IMAGE_PAYLOAD] ? null : EditionImageUrl::from((string)$payload[self::EDITION_IMAGE_PAYLOAD]);
        $this->condition = null === $payload[self::EDITION_CONDITION_PAYLOAD] ? null : EditionCondition::from((string)$payload[self::EDITION_CONDITION_PAYLOAD]);
        $this->pages = null === $payload[self::EDITION_PAGES_PAYLOAD] ? null : EditionPages::from((int)$payload[self::EDITION_PAGES_PAYLOAD]);
        $this->city = EditionCity::from((string)$payload[self::EDITION_CITY_PAYLOAD]);
        $this->isOnLibrary = EditionIsOnLibrary::from((bool)$payload[self::EDITION_IS_ON_LIBRARY]);
    }

    public function editionId(): Uuid
    {
        return $this->editionId;
    }

    public function year(): EditionYear
    {
        return $this->year;
    }

    public function publisherId(): Uuid
    {
        return $this->publisherId;
    }

    public function bookId(): Uuid
    {
        return $this->bookId;
    }

    public function googleBooksId(): ?EditionGoogleBooksId
    {
        return $this->googleBooksId;
    }

    public function isbn(): EditionISBN
    {
        return $this->isbn;
    }

    public function title(): EditionTitle
    {
        return $this->title;
    }

    public function subtitle(): ?EditionSubtitle
    {
        return $this->subtitle;
    }

    public function locale(): EditionLocale
    {
        return $this->locale;
    }

    public function image(): ?EditionImageUrl
    {
        return $this->imageUrl;
    }

    public function resource()
    {
        return $this->resource;
    }
    public function condition(): ?EditionCondition
    {
        return $this->condition;
    }

    public function city(): EditionCity
    {
        return $this->city;
    }

    public function pages(): ?EditionPages
    {
        return $this->pages;
    }

    public function isOnLibrary(): EditionIsOnLibrary
    {
        return $this->isOnLibrary;
    }
}
