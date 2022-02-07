<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Country;

use Colybri\Criteria\Domain\Criteria;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

interface CountryRepository
{
    public function find(Uuid $id): ?Country;

    public function match(Criteria $criteria): array;

}