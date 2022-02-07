<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Book;

use Colybri\Criteria\Domain\Criteria;

interface BookRepository
{
    public function match(Criteria $criteria): array;
}