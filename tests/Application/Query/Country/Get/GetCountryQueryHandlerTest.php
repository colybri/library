<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Query\Country\Get;

use Colybri\Library\Application\Query\Country\Get\GetCountryQuery;
use Colybri\Library\Application\Query\Country\Get\GetCountryQueryHandler;
use Colybri\Library\Domain\Model\Country\CountryRepository;
use Colybri\Library\Domain\Model\Country\Exception\CountryDoesNotExistException;
use Colybri\Library\Domain\Service\Country\CountryFinder;
use Colybri\Library\Domain\Service\Country\CountrySearcher;
use Colybri\Library\Tests\Mock\Domain\Model\Country\CountryObjectMother;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetCountryQueryHandlerTest extends TestCase
{
    private MockObject $repository;

    private GetCountryQueryHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CountryRepository::class);

        $this->handler = new GetCountryQueryHandler(
            new CountryFinder(
                new CountrySearcher($this->repository)
            )
        );
    }

    private function query(Uuid $id): GetCountryQuery
    {
        return GetCountryQuery::fromPayload(
            Uuid::v4(),
            [
                GetCountryQuery::COUNTRY_ID_PAYLOAD => $id->value(),
            ],
        );
    }

    /**
     * @test
     */
    public function given_existing_country_then_retrieve_it(): void
    {
        $countryId = Uuid::v4();
        $mother = new CountryObjectMother(id: $countryId);

        $this->repository->expects($this->once())
            ->method('find')
            ->with($countryId)
            ->willReturn($mother->build());


        ($this->handler)($this->query($countryId));
    }

    /**
     * @test
     */
    public function given_not_existing_country_then_throw_exception(): void
    {
        $this->expectException(CountryDoesNotExistException::class);

        $countryId = Uuid::v4();

        $this->repository->expects($this->once())
            ->method('find')
            ->with($countryId)
            ->willReturn(null);

        ($this->handler)($this->query($countryId));
    }
}
