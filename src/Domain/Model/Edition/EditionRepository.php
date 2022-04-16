<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Edition;

use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

interface EditionRepository
{
    public function find(Uuid $id): ?Edition;
}
