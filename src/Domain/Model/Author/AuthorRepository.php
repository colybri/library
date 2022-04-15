<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Author;

use Colybri\Criteria\Domain\Criteria;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

interface AuthorRepository
{
    public function find(Uuid $id): ?Author;

    public function insert(Author $author): void;

    public function update(Author $author): void;

    public function match(Criteria $criteria): array;

    public function delete(Uuid $id): void;
}
