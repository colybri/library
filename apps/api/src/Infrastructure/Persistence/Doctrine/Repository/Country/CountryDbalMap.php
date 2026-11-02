<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\Country;

use Colybri\Criteria\Infrastructure\Adapter\EntityMap;
use Colybri\Library\Domain\Model\Country\ValueObject\CountryAlpha2Code;
use Colybri\Library\Domain\Model\Country\ValueObject\CountryName;

final class CountryDbalMap implements EntityMap
{
    private const FIELDS = [
        CountryName::class => 'en_short_name',
        CountryAlpha2Code::class => 'alpha_2_code'
    ];

    private const TABLE = 'countries';

    public function map(string $attribute): string
    {
        return self::FIELDS[$attribute];
    }

    public static function table(): string
    {
        return self::TABLE;
    }
}
