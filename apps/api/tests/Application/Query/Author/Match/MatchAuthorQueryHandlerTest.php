<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Query\Author\Match;

use PHPUnit\Framework\Attributes\Test;
use Colybri\Library\Application\Query\Author\Match\MatchAuthorQuery;
use Colybri\Library\Application\Query\Author\Match\MatchAuthorQueryHandler;
use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorFirstName;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorLastName;
use Colybri\Library\Domain\Service\Author\AuthorMatcher;
use Colybri\Library\Domain\Service\Shared\SimilarityAligner;
use Colybri\Library\Tests\Mock\Domain\Model\Author\AuthorObjectMother;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MatchAuthorQueryHandlerTest extends TestCase
{
    private MatchAuthorQueryHandler $handler;
    private MockObject $authorRepository;

    protected function setUp(): void
    {
        $this->authorRepository = $this->createMock(AuthorRepository::class);
        $this->handler = new MatchAuthorQueryHandler(
            new AuthorMatcher($this->authorRepository, new SimilarityAligner())
        );
    }

    #[Test]
    public function given_authors_when_search_then_order_result(): void
    {
        list($mockOne, $mockTwo) = $this->authors();

        $this->authorRepository->expects(static::once())
            ->method('match')
            ->willReturn([$mockOne, $mockTwo]);

        $response = ($this->handler)($this->query(50));

        self::assertSame(2, $response->total());
        self::assertSame(0, $response->offset());
        self::assertSame(50, $response->limit());
        self::assertCount(2, $response->items());
        self::assertInstanceOf(Author::class, $response->items()[1]);
        self::assertSame($response->items()[0]->firstName(), $mockTwo->firstName());
        self::assertSame($response->items()[0]->lastName(), $mockTwo->lastName());
    }

    #[Test]
    public function given_authors_when_search_then_paginate_result(): void
    {

        list($mockOne, $mockTwo) = $this->authors();

        $this->authorRepository->expects(static::once())
            ->method('match')
            ->willReturn([$mockOne, $mockTwo]);

        $response = ($this->handler)($this->query(1));

        self::assertSame(2, $response->total());
        self::assertSame(0, $response->offset());
        self::assertSame(1, $response->limit());
        self::assertCount(1, $response->items());
        self::assertInstanceOf(Author::class, $response->items()[0]);
        self::assertSame($response->items()[0]->firstName(), $mockTwo->firstName());
        self::assertSame($response->items()[0]->lastName(), $mockTwo->lastName());
    }

    #[Test]
    public function given_a_long_phrase_when_search_then_queries_are_bounded(): void
    {
        $criteria = [];
        $this->authorRepository->expects(static::exactly(AuthorMatcher::MAX_KEYWORDS))
            ->method('match')
            ->willReturnCallback(static function ($criterion) use (&$criteria): array {
                $criteria[] = $criterion;

                return [];
            });

        ($this->handler)($this->query(10, 'a  a b c d e f g h i j k'));

        foreach ($criteria as $criterion) {
            self::assertSame(AuthorMatcher::MAX_MATCHES_PER_KEYWORD, $criterion->limit());
        }
    }

    private function query($limit, string $keywords = 'Barruel'): MatchAuthorQuery
    {
        $query = MatchAuthorQuery::fromPayload(
            Uuid::from('2e9d7383-775f-4786-a7f3-b9c560a161ba'),
            [
                'offset' => 0,
                'limit' => $limit,
                'keywords' => $keywords
            ],
        );
        return $query;
    }

    private function authors(): array
    {
        $mockOne = new AuthorObjectMother(firstName: AuthorFirstName::from('Gershom'), lastName: AuthorLastName::from('Scholem'));
        $mockTwo = new AuthorObjectMother(firstName: AuthorFirstName::from('Augustin'), lastName: AuthorLastName::from('Barruel'));

        return [$mockOne->build(), $mockTwo->build()];
    }
}
