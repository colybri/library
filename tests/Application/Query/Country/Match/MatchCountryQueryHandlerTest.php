<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Query\Country\Match;

use Colybri\Library\Application\Query\Country\Match\MatchCountryQuery;
use Colybri\Library\Application\Query\Country\Match\MatchCountryQueryHandler;
use Colybri\Library\Domain\Model\Country\Country;
use Colybri\Library\Domain\Model\Country\CountryRepository;
use Colybri\Library\Tests\Mock\Domain\Model\Country\CountryObjectMother;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MatchCountryQueryHandlerTest extends TestCase
{
    private MatchCountryQueryHandler $handler;
    private MockObject $countryRepository;

    protected function setUp(): void
    {
        $this->countryRepository = $this->createMock(CountryRepository::class);
        $this->handler = new MatchCountryQueryHandler(
            $this->countryRepository
        );
    }


    /**
     * @test
     */
    public function given_authors_when_search_then_paginate_result(): void
    {

        $countryMock = new CountryObjectMother();

        $this->countryRepository->expects($this->once())
            ->method('match')
            ->willReturn([$countryMock->build()]);

        $this->countryRepository->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $response = ($this->handler)($this->query());

        self::assertSame(1, $response->total());
        self::assertSame(0, $response->offset());
        self::assertSame(1, $response->limit());
        self::assertCount(1, $response->items());
        self::assertInstanceOf(Country::class, $response->items()[0]);
    }


    private function query(): MatchCountryQuery
    {
        $query = MatchCountryQuery::fromPayload(
            Uuid::from('2e9d7383-775f-4786-a7f3-b9c560a161ba'),
            [
                'offset' => 0,
                'limit' => 1,
                'keywords' => 'Papua'
            ],
        );
        return $query;
    }
}
