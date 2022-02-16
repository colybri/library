<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Book\Bulk;

use Assert\Assert;
use Biblys\Isbn\Isbn;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Book\Book;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Application\Query;
use PcComponentes\TopicGenerator\Topic;

final class SearchBookQuery extends Query
{
    private const VERSION = '1';
    private const NAME = 'search_book';

    public const BOOK_TITLE_PAYLOAD = 'title';
    public const AUTHOR_PAYLOAD = 'author';
    public const PUBLISHER_PAYLOAD = 'publisher';
    public const SUBJECT_PAYLOAD = 'subject';
    public const ISBN_PAYLOAD = 'isbn';

    private ?string $title;
    private ?string $author;
    private ?string $publisher;
    private ?string $subject;
    private ?string $isbn;

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
            ->that($payload, 'payload')
            ->keyExists(SearchBookQuery::BOOK_TITLE_PAYLOAD)
            ->keyExists(SearchBookQuery::AUTHOR_PAYLOAD)
            ->keyExists(SearchBookQuery::PUBLISHER_PAYLOAD)
            ->keyExists(SearchBookQuery::SUBJECT_PAYLOAD)
            ->keyExists(SearchBookQuery::ISBN_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[SearchBookQuery::BOOK_TITLE_PAYLOAD])->nullOr()->string()
            ->that($payload[SearchBookQuery::AUTHOR_PAYLOAD])->nullOr()->string()
            ->that($payload[SearchBookQuery::PUBLISHER_PAYLOAD])->nullOr()->string()
            ->that($payload[SearchBookQuery::SUBJECT_PAYLOAD])->nullOr()->string()
            ->that($payload[SearchBookQuery::ISBN_PAYLOAD])->nullOr()->string()
            ->verifyNow();


        $this->title = null === $payload[self::BOOK_TITLE_PAYLOAD] ? null : (string) $payload[self::BOOK_TITLE_PAYLOAD];
        $this->author = null === $payload[self::AUTHOR_PAYLOAD] ? null : (string) $payload[self::AUTHOR_PAYLOAD];
        $this->publisher = null === $payload[self::PUBLISHER_PAYLOAD] ? null : (string) $payload[self::PUBLISHER_PAYLOAD];
        $this->subject = null === $payload[self::SUBJECT_PAYLOAD] ? null : (string) $payload[self::SUBJECT_PAYLOAD];
        $this->isbn = null === $payload[self::ISBN_PAYLOAD] ? null : (string) $payload[self::ISBN_PAYLOAD];

    }

    public function title():? string
    {
        return $this->title;
    }

    public function author():? string
    {
        return $this->author;
    }

    public function publisher():? string
    {
        return $this->publisher;
    }

    public function subject():? string
    {
        return $this->subject;
    }

    public function isbn(): ?string
    {
        return $this->isbn;
    }
}