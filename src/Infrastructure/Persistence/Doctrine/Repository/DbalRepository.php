<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Connection;

abstract class DbalRepository
{
    public function __construct(protected Connection $connectionRead, protected Connection $connectionWrite)
    {
    }
}