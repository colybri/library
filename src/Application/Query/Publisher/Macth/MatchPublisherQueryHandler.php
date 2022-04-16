<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Publisher\Macth;

use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Domain\Disjunction;
use Colybri\Criteria\Domain\Filter;
use Colybri\Criteria\Domain\FilterField;
use Colybri\Criteria\Domain\FilterOperator;
use Colybri\Criteria\Domain\Filters;
use Colybri\Criteria\Domain\FilterValue;
use Colybri\Criteria\Domain\Order;
use Colybri\Criteria\Domain\OrderBy;
use Colybri\Criteria\Domain\OrderType;
use Colybri\Library\Application\Query\Shared\ListResponse;
use Colybri\Library\Domain\Model\Publisher\PublisherRepository;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherCity;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherName;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class MatchPublisherQueryHandler implements MessageHandlerInterface
{
    public function __construct(private PublisherRepository $publisherRepository)
    {
    }

    public function __invoke(MatchPublisherQuery $query): ListResponse
    {
        $filters = $this->filters($query);

        return ListResponse::fromPaginatedList(
            $this->publisherRepository->match($this->getCriteria($filters, $query)),
            $this->publisherRepository->count($this->getCriteria($filters, $query)),
            $query->offset(),
            $query->limit()
        );
    }

    public function filters(MatchPublisherQuery $query): Disjunction
    {
        return Disjunction::fromFilters(
            Filter::from(
                FilterField::from(PublisherName::class),
                FilterOperator::Contains,
                FilterValue::from($query->match())
            ),
            Filter::from(
                FilterField::from(PublisherCity::class),
                FilterOperator::Contains,
                FilterValue::from($query->match())
            )
        );
    }

    private function getCriteria(Disjunction $filters, MatchPublisherQuery $query): Criteria
    {
        return new Criteria(
            Filters::from($filters),
            Order::from(OrderBy::from(PublisherName::class), OrderType::Desc),
            $query->offset(),
            $query->limit()
        );
    }
}
