<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Publisher;

use Colybri\Criteria\Domain\Criteria;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

interface PublisherRepository
{
    public function find(Uuid $id): ?Publisher;

    public function insert(Publisher $publisher): void;

    public function update(Publisher $publisher): void;

    public function match(Criteria $criteria): array;

    public function count(Criteria $criteria): int;

    public function delete(Uuid $id): void;
}
