<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Infrastructure\Persistence\Doctrine\Repository\Author;

use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Infrastructure\Adapter\Dbal\CriteriaDbalAdapter;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Service\Author\AuthorMatcher;
use Colybri\Library\Domain\Service\Shared\SimilarityAligner;
use Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\Author\AuthorDbalMap;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AuthorDbalMapTest extends TestCase
{
    #[Test]
    public function given_a_keyword_when_matching_authors_then_searches_first_or_last_name_columns(): void
    {
        $criteria = null;
        $repository = $this->createMock(AuthorRepository::class);
        $repository->method('match')->willReturnCallback(
            function (Criteria $received) use (&$criteria): array {
                $criteria = $received;

                return [];
            }
        );

        (new AuthorMatcher($repository, new SimilarityAligner()))->execute('Guin');

        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn(new PostgreSQLPlatform());
        $queryBuilder = (new QueryBuilder($connection))->select('*')->from(AuthorDbalMap::table());
        (new CriteriaDbalAdapter($queryBuilder, new AuthorDbalMap()))->build($criteria);

        self::assertSame(
            'SELECT * FROM authors WHERE ( LOWER(first_name) LIKE :first_name1 OR LOWER(last_name) LIKE :last_name2  )',
            $queryBuilder->getSQL()
        );
        self::assertSame(['first_name1' => '%guin%', 'last_name2' => '%guin%'], $queryBuilder->getParameters());
    }
}
