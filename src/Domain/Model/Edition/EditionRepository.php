<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Edition;

use Colybri\Criteria\Domain\Criteria;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

interface EditionRepository
{
    public function find(Uuid $id): ?Edition;

    public function match(Criteria $criteria): array;

    public function insert(Edition $edition): void;

    public function delete(Uuid $id): void;
}
