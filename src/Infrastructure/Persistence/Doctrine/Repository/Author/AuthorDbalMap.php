<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\Author;

use Colybri\Criteria\Infrastructure\Adapter\EntityMap;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorName;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorIsPseudonymOf;
use Colybri\Library\Domain\Model\Book\Book;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\Book\BookDbalMap;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class AuthorDbalMap implements EntityMap
{
    private const FIELDS = [
        Uuid::class => 'id',
        AuthorName::class => 'name',
        AuthorIsPseudonymOf::class => 'is_pseudonym_of',
        AuthorBornAt::class => 'born_year',
        AuthorDeathAt::class => 'death_year'
    ];

    private const TABLE = 'authors';

    public function map(string $attribute): string
    {
        return self::FIELDS[$attribute];
    }

    public static function table(): string
    {
        return self::TABLE;
    }
}
