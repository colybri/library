<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Query\Shared;

use Assert\InvalidArgumentException;
use Colybri\Library\Application\Query\Shared\ListResponse;
use PHPUnit\Framework\TestCase;

final class ListResponseTest extends TestCase
{
    /**
     * @test
     */
    public function given_unpaginated_list_when_is_created_then_should_return_paginate_result(): void
    {
        $list = ListResponse::fromUnpaginatedList([1, 2, 3, 4, 5, 6, 7], 2, 3);

        self::assertCount(3, $list->items());
        self::assertSame(7, $list->total());
        self::assertSame(2, $list->offset());
        self::assertSame(3, $list->items()[0]);
        self::assertSame(5, $list->items()[count($list->items()) - 1]);
    }

    /**
     * @test
     */
    public function given_paginated_list_when_is_created_then_should_return_same_values(): void
    {
        $list = ListResponse::fromPaginatedList([1, 2, 3, 4, 5], 200, 0, 5);

        self::assertCount(5, $list->items());
        self::assertSame(200, $list->total());
        self::assertSame(0, $list->offset());
        self::assertSame(5, $list->limit());
    }

    /**
     * @test
     */
    public function given_invalid_paginated_list_when_is_created_then_should_throws_exception(): void
    {
        self::expectException(InvalidArgumentException::class);
        ListResponse::fromPaginatedList([1, 2, 3, 4, 5], 200, 0, 2);
    }

    /**
     * @test
     */
    public function given_paginated_list_when_is_serializated_then_should_should_contains_list_keys(): void
    {
        $list = (array)json_decode(json_encode(ListResponse::fromPaginatedList([1, 2, 3, 4, 5], 200, 0, 5)));

        self::assertArrayHasKey('total', $list);
        self::assertArrayHasKey('offset', $list);
        self::assertArrayHasKey('limit', $list);
        self::assertArrayHasKey('items', $list);
    }
}
