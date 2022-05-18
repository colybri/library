<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\Book;

use Colybri\Criteria\Infrastructure\Adapter\EntityMap;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIds;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIsPseudo;
use Colybri\Library\Domain\Model\Book\ValueObject\BookIsOnWishList;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYear;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYearIsEstimated;
use Colybri\Library\Domain\Model\Book\ValueObject\BookSubtitle;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class BookDbalMap implements EntityMap
{
    private const FIELDS = [
        Uuid::class => 'id',
        BookTitle::class => 'title',
        BookSubtitle::class => 'subtitle',
        BookAuthorIds::class => 'author_ids',
        BookAuthorIsPseudo::class => 'is_pseudo_author',
        BookPublishYear::class => 'publish_year',
        BookPublishYearIsEstimated::class => 'is_estimated_publish_year',
        BookIsOnWishList::class => 'is_on_wish_list',

    ];

    private const TABLE = 'books';

    public function map(string $attribute): string
    {
        return self::FIELDS[$attribute];
    }

    public static function table(): string
    {
        return self::TABLE;
    }
}
