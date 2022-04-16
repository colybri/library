<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Query\Publisher\Match;

use Colybri\Library\Application\Query\Publisher\Macth\MatchPublisherQuery;
use Colybri\Library\Application\Query\Publisher\Macth\MatchPublisherQueryHandler;
use Colybri\Library\Domain\Model\Publisher\Publisher;
use Colybri\Library\Domain\Model\Publisher\PublisherRepository;
use Colybri\Library\Tests\Mock\Domain\Model\Publisher\PublisherObjectMother;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
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


    /**
     * @test
     */
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
