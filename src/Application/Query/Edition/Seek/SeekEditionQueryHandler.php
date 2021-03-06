<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Edition\Seek;

use Colybri\Criteria\Domain\Conjunction;
use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Domain\Filter;
use Colybri\Criteria\Domain\FilterField;
use Colybri\Criteria\Domain\FilterOperator;
use Colybri\Criteria\Domain\Filters;
use Colybri\Criteria\Domain\FilterValue;
use Colybri\Criteria\Domain\Order;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorName;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use Colybri\Library\Domain\Model\Edition\EditionRepository;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionISBN;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherName;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SeekEditionQueryHandler implements MessageHandlerInterface
{
    public function __construct(private readonly EditionRepository $seekEditionRepository)
    {
    }

    public function __invoke(SeekEditionQuery $query): array
    {
        $filters = $this->filters($query);

        return $this->seekEditionRepository->match(new Criteria(
            Filters::from($filters),
            Order::none(),
            null,
            null
        ));
    }

    public function filters(SeekEditionQuery $query): Conjunction
    {
        $filters = [];

        if (null !== $query->title()) {
            $filters[] = Filter::from(
                FilterField::from(BookTitle::class),
                FilterOperator::Contains,
                FilterValue::from($query->title())
            );
        }

        if (null !== $query->author()) {
            $filters[] = Filter::from(
                FilterField::from(AuthorName::class),
                FilterOperator::Contains,
                FilterValue::from($query->author())
            );
        }

        if (null !== $query->publisher()) {
            $filters[] = Filter::from(
                FilterField::from(PublisherName::class),
                FilterOperator::Contains,
                FilterValue::from($query->publisher())
            );
        }

        if (null !== $query->isbn()) {
            $filters[] = Filter::from(
                FilterField::from(EditionISBN::class),
                FilterOperator::Contains,
                FilterValue::from($query->isbn())
            );
        }
        return Conjunction::fromFilters(
            ...$filters
        );
    }
}
