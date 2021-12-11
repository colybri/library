<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\Author;

use Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\DbalRepository;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\Author;

class AuthorDbalRepository extends DbalRepository implements AuthorRepository
{
    public function insert(Author $author): void
    {
        $sql = "
            INSERT into authors (
                id, 
                firstName
            ) VALUES (
                :id,
                :firstName     
            )
        ";

        $statement = $this->connection->prepare($sql);
        $statement->bindValue('id', $author->id()->value());
        $statement->bindValue('firstName', $author->firstName()->value());

        $statement->executeQuery();
    }
}