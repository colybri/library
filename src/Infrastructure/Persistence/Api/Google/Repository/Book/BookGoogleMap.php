<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Api\Google\Repository\Book;

use Colybri\Criteria\Infrastructure\Adapter\EntityMap;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorName;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionISBN;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherName;

class BookGoogleMap implements EntityMap
{
    private const FIELDS = [
        BookTitle::class => 'intitle',
        AuthorName::class => 'inauthor',
        PublisherName::class => 'inpublisher',
        //subject
        EditionISBN::class => 'isbn',
        //lccn
        //oclc
    ];

    private const TABLE = 'volumes';

    public function map(string $attribute): string
    {
        return self::FIELDS[$attribute];
    }

    public static function table(): string
    {
        return self::TABLE;
    }
}
