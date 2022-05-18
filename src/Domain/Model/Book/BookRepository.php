<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Book;

use Colybri\Criteria\Domain\Criteria;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

interface BookRepository
{
    public function find(Uuid $id): ?Book;

    public function match(Criteria $criteria): array;

    public function insert(Book $book): void;

    public function update(Book $book): void;

    public function delete(Uuid $id): void;
}
