<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Shared;

use Assert\Assert;

final class ListResponse implements \JsonSerializable
{
    private function __construct(private array $items, private $total, private int $offset, private int $limit)
    {
    }

    public static function fromUnpaginatedList(array $items, int $offset, int $limit): ListResponse
    {
        return new ListResponse(array_slice($items, $offset, $limit), count($items), $offset, $limit);
    }

    public static function fromPaginatedList(array $items, int $total, int $offset, int $limit): ListResponse
    {
        Assert::that($limit)->greaterOrEqualThan(count($items));

        return new ListResponse($items, $total, $offset, $limit);
    }

    public function jsonSerialize(): array
    {
        return [
            'total' => $this->total(),
            'offset' => $this->offset(),
            'limit' => $this->limit(),
            'items' => $this->items(),
        ];
    }

    public function items(): array
    {
        return $this->items;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function offset(): int
    {
        return $this->offset;
    }

    public function limit(): int
    {
        return $this->limit;
    }
}
