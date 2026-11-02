<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Infrastructure\Persistence\Doctrine\Repository\Book;

use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Domain\Filter;
use Colybri\Criteria\Domain\FilterField;
use Colybri\Criteria\Domain\FilterOperator;
use Colybri\Criteria\Domain\Filters;
use Colybri\Criteria\Domain\FilterValue;
use Colybri\Criteria\Domain\Order;
use Colybri\Criteria\Infrastructure\Adapter\Dbal\CriteriaDbalAdapter;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIds;
use Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\Book\BookDbalMap;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BookDbalMapTest extends TestCase
{
    #[Test]
    public function given_book_author_ids_when_mapping_then_uses_the_authors_ids_column(): void
    {
        self::assertSame('authors_ids', (new BookDbalMap())->map(BookAuthorIds::class));
    }

    #[Test]
    public function given_an_author_filter_when_building_the_query_then_filters_on_authors_ids(): void
    {
        $criteria = new Criteria(
            Filters::from(
                Filter::from(
                    FilterField::from(BookAuthorIds::class),
                    FilterOperator::Equal,
                    FilterValue::from('6f2d1c3a-1b9e-4a55-9a43-2f3c1d0e9b7a')
                )
            ),
            Order::none(),
            null,
            null
        );

        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn(new PostgreSQLPlatform());
        $queryBuilder = (new QueryBuilder($connection))->select('*')->from(BookDbalMap::table());
        (new CriteriaDbalAdapter($queryBuilder, new BookDbalMap()))->build($criteria);

        self::assertStringContainsString('authors_ids', $queryBuilder->getSQL());
        self::assertStringNotContainsString(' author_ids', $queryBuilder->getSQL());
    }
}
