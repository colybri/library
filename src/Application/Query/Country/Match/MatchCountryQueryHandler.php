<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Country\Match;

use Colybri\Library\Application\Query\Shared\ListResponse;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Domain\Filters;
use Colybri\Criteria\Domain\Disjunction;
use Colybri\Criteria\Domain\Filter;
use Colybri\Criteria\Domain\FilterField;
use Colybri\Criteria\Domain\FilterOperator;
use Colybri\Criteria\Domain\FilterValue;
use Colybri\Criteria\Domain\Order;
use Colybri\Criteria\Domain\OrderBy;
use Colybri\Criteria\Domain\OrderType;
use Colybri\Library\Domain\Model\Country\CountryRepository;
use Colybri\Library\Domain\Model\Country\ValueObject\CountryAlpha2Code;
use Colybri\Library\Domain\Model\Country\ValueObject\CountryName;

final class MatchCountryQueryHandler implements MessageHandlerInterface
{
    public function __construct(private CountryRepository $countryRepository)
    {
    }

    public function __invoke(MatchCountryQuery $query): ListResponse
    {
        $filters = $this->filters($query);

        return ListResponse::fromPaginatedList(
            $this->countryRepository->match($this->getCriteria($filters, $query)),
            $this->countryRepository->count($this->getCriteria($filters, $query)),
            $query->offset(),
            $query->limit()
        );
    }

    public function filters(MatchCountryQuery $query): Disjunction
    {
        return Disjunction::fromFilters(
            Filter::from(
                FilterField::from(CountryName::class),
                FilterOperator::Contains,
                FilterValue::from($query->match())
            ),
            Filter::from(
                FilterField::from(CountryAlpha2Code::class),
                FilterOperator::Contains,
                FilterValue::from($query->match())
            )
        );
    }

    private function getCriteria(Disjunction $filters, MatchCountryQuery $query): Criteria
    {
        return new Criteria(
            Filters::from($filters),
            Order::from(OrderBy::from(CountryName::class), OrderType::Desc),
            $query->offset(),
            $query->limit()
        );
    }
}
