<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\Publisher;

use Colybri\Criteria\Infrastructure\Adapter\EntityMap;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherCity;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherFoundationYear;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherName;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class PublisherDbalMap implements EntityMap
{
    private const FIELDS = [
        PublisherName::class => 'name',
        PublisherCity::class => 'city',
        Uuid::class => 'country_id',
        PublisherFoundationYear::class => 'foundation_year'
    ];

    private const TABLE = 'publishers';

    public function map(string $attribute): string
    {
        return self::FIELDS[$attribute];
    }

    public static function table(): string
    {
        return self::TABLE;
    }
}