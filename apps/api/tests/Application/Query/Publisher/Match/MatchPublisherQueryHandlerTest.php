<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Query\Publisher\Match;

use PHPUnit\Framework\Attributes\Test;
use Colybri\Criteria\Domain\Criteria;
use Colybri\Library\Application\Query\Publisher\Macth\MatchPublisherQuery;
use Colybri\Library\Application\Query\Publisher\Macth\MatchPublisherQueryHandler;
use Colybri\Library\Domain\Model\Publisher\Publisher;
use Colybri\Library\Domain\Model\Publisher\PublisherRepository;
use Colybri\Library\Tests\Mock\Domain\Model\Publisher\PublisherObjectMother;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MatchPublisherQueryHandlerTest extends TestCase
{
    private MatchPublisherQueryHandler $handler;
    private MockObject $publisherRepository;

    protected function setUp(): void
    {
        $this->publisherRepository = $this->createMock(PublisherRepository::class);
        $this->handler = new MatchPublisherQueryHandler(
            $this->publisherRepository
        );
    }


    #[Test]
    public function given_publishers_when_search_then_paginate_result(): void
    {

        $mother = new PublisherObjectMother();

        $this->publisherRepository->expects($this->once())
            ->method('match')
            ->willReturn([$mother->build()]);

        $this->publisherRepository->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $response = ($this->handler)($this->query());

        self::assertSame(1, $response->total());
        self::assertSame(0, $response->offset());
        self::assertSame(1, $response->limit());
        self::assertCount(1, $response->items());
        self::assertInstanceOf(Publisher::class, $response->items()[0]);
    }


    #[Test]
    public function given_more_matches_than_the_page_when_search_then_total_counts_every_match(): void
    {
        $mother = new PublisherObjectMother();

        $this->publisherRepository->expects($this->once())
            ->method('match')
            ->with($this->callback(static fn (Criteria $criteria): bool => 0 === $criteria->offset() && 1 === $criteria->limit()))
            ->willReturn([$mother->build()]);

        // The count must not be limited to the page, or total would never exceed limit
        $this->publisherRepository->expects($this->once())
            ->method('count')
            ->with($this->callback(static fn (Criteria $criteria): bool => null === $criteria->offset() && null === $criteria->limit()))
            ->willReturn(7);

        $response = ($this->handler)($this->query());

        self::assertSame(7, $response->total());
        self::assertCount(1, $response->items());
    }

    private function query(): MatchPublisherQuery
    {
        $query = MatchPublisherQuery::fromPayload(
            Uuid::from('2e9d7383-775f-4786-a7f3-b9c560a161ba'),
            [
                'offset' => 0,
                'limit' => 1,
                'keywords' => 'Mexico'
            ],
        );
        return $query;
    }
}
