<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Infrastructure\Persistence\Api\Google;

use Colybri\Criteria\Domain\Conjunction;
use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Domain\Filter;
use Colybri\Criteria\Domain\FilterField;
use Colybri\Criteria\Domain\FilterOperator;
use Colybri\Criteria\Domain\Filters;
use Colybri\Criteria\Domain\FilterValue;
use Colybri\Criteria\Domain\Order;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorFirstName;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use Colybri\Library\Infrastructure\Persistence\Api\Google\CriteriaGoogleBookAdapter;
use Colybri\Library\Infrastructure\Persistence\Api\Google\Repository\Edition\EditionGoogleMap;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CriteriaGoogleBookAdapterTest extends TestCase
{
    #[Test]
    public function given_several_filters_when_building_then_joins_them_and_encodes_the_values(): void
    {
        $criteria = new Criteria(
            Filters::from(
                Conjunction::fromFilters(
                    Filter::from(FilterField::from(BookTitle::class), FilterOperator::Contains, FilterValue::from('El nombre de la rosa')),
                    Filter::from(FilterField::from(AuthorFirstName::class), FilterOperator::Contains, FilterValue::from('Umberto Eco')),
                )
            ),
            Order::none(),
            20,
            10
        );

        $path = (new CriteriaGoogleBookAdapter('?q=', new EditionGoogleMap()))->build($criteria);

        self::assertSame(
            '?q=intitle:El%20nombre%20de%20la%20rosa+inauthor:Umberto%20Eco&startIndex=20&maxResults=10',
            $path
        );
    }

    #[Test]
    public function given_a_single_filter_when_building_then_it_is_in_the_query(): void
    {
        $criteria = new Criteria(
            Filters::from(
                Filter::from(FilterField::from(BookTitle::class), FilterOperator::Contains, FilterValue::from('Dune'))
            ),
            Order::none(),
            null,
            null
        );

        $path = (new CriteriaGoogleBookAdapter('?q=', new EditionGoogleMap()))->build($criteria);

        self::assertSame('?q=intitle:Dune', $path);
    }
}
