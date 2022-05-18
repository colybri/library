<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Edition\Seek;

use Assert\Assert;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Book\Book;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Application\Query;
use PcComponentes\TopicGenerator\Topic;

final class SeekEditionQuery extends Query
{
    private const VERSION = '1';
    private const NAME = 'search_book';

    public const EDITION_TITLE_PAYLOAD = 'title';
    public const EDITION_AUTHOR_PAYLOAD = 'author';
    public const EDITION_PUBLISHER_PAYLOAD = 'publisher';
    public const EDITION_ISBN_PAYLOAD = 'isbn';

    private ?string $title;
    private ?string $author;
    private ?string $publisher;
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
            ->keyExists(SeekEditionQuery::EDITION_TITLE_PAYLOAD)
            ->keyExists(SeekEditionQuery::EDITION_AUTHOR_PAYLOAD)
            ->keyExists(SeekEditionQuery::EDITION_PUBLISHER_PAYLOAD)
            ->keyExists(SeekEditionQuery::EDITION_ISBN_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[SeekEditionQuery::EDITION_TITLE_PAYLOAD])->nullOr()->string()
            ->that($payload[SeekEditionQuery::EDITION_AUTHOR_PAYLOAD])->nullOr()->string()
            ->that($payload[SeekEditionQuery::EDITION_PUBLISHER_PAYLOAD])->nullOr()->string()
            ->that($payload[SeekEditionQuery::EDITION_ISBN_PAYLOAD])->nullOr()->string()
            ->verifyNow();

        $filters = [
            $payload[SeekEditionQuery::EDITION_TITLE_PAYLOAD],
            $payload[SeekEditionQuery::EDITION_AUTHOR_PAYLOAD],
            $payload[SeekEditionQuery::EDITION_PUBLISHER_PAYLOAD],
            $payload[SeekEditionQuery::EDITION_ISBN_PAYLOAD]
        ];

        $this->ensureAtLeastExistOneFilter($filters);

        $this->title = null === $payload[self::EDITION_TITLE_PAYLOAD] ? null : (string) $payload[self::EDITION_TITLE_PAYLOAD];
        $this->author = null === $payload[self::EDITION_AUTHOR_PAYLOAD] ? null : (string) $payload[self::EDITION_AUTHOR_PAYLOAD];
        $this->publisher = null === $payload[self::EDITION_PUBLISHER_PAYLOAD] ? null : (string) $payload[self::EDITION_PUBLISHER_PAYLOAD];
        $this->isbn = null === $payload[self::EDITION_ISBN_PAYLOAD] ? null : (string) $payload[self::EDITION_ISBN_PAYLOAD];
    }

    private function ensureAtLeastExistOneFilter($payload)
    {
        if (
            empty(array_filter($payload, function ($a) {
                return $a !== null;
            }))
        ) {
            throw new \InvalidArgumentException('At least one filter have to be sent');
        }
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function author(): ?string
    {
        return $this->author;
    }

    public function publisher(): ?string
    {
        return $this->publisher;
    }

    public function isbn(): ?string
    {
        return $this->isbn;
    }
}
