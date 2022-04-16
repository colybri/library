<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Query\Author\Match;

use Colybri\Library\Application\Query\Author\Match\MatchAuthorQuery;
use Colybri\Library\Application\Query\Author\Match\MatchAuthorQueryHandler;
use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorName;
use Colybri\Library\Domain\Service\Author\AuthorMatcher;
use Colybri\Library\Domain\Service\Shared\SimilarityAligner;
use Colybri\Library\Tests\Mock\Domain\Model\Author\AuthorObjectMother;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
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

    /**
     * @test
     */
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
        self::assertSame($response->items()[0]->name(), $mockTwo->name());
    }

    /**
     * @test
     */
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
        self::assertSame($response->items()[0]->name(), $mockTwo->name());
    }

    private function query($limit): MatchAuthorQuery
    {
        $query = MatchAuthorQuery::fromPayload(
            Uuid::from('2e9d7383-775f-4786-a7f3-b9c560a161ba'),
            [
                'offset' => 0,
                'limit' => $limit,
                'keywords' => 'Barruel'
            ],
        );
        return $query;
    }

    private function authors(): array
    {
        $name = AuthorName::from('Augustin Barruel');

        $mockOne = new AuthorObjectMother(name: AuthorName::from('Gershom Scholem'));
        $mockTwo = new AuthorObjectMother(name: $name);

        return [$mockOne->build(), $mockTwo->build()];
    }
}
