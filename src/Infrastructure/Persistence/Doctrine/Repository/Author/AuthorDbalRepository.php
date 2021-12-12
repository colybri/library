<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\Author;

use Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\DbalRepository;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\Author;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class AuthorDbalRepository extends DbalRepository implements AuthorRepository
{
    public function find(Uuid $id): ?Author
    {
        $sql = "
            SELECT * from authors 
             WHERE (
                id = :id
            )
        ";

        $statement = $this->connection->prepare($sql);

        return $statement->executeQuery();
    }

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
        $statement->bindValue('first_name', $author->firstName()->value());
        $statement->bindValue('last_name', $author->lastName()?->value());
        $statement->bindValue('is_pseudonym_of', $author->firstName()->value());
        $statement->bindValue('is_pseudo', $author->firstName()->value());
        $statement->bindValue('born_at', $author->firstName()->value());
        $statement->bindValue('death_at', $author->firstName()->value());
        $statement->bindValue('country_id', $author->firstName()->value());

        $statement->executeQuery();
    }

    public function update(Author $author): void
    {
        // TODO: Implement update() method.
    }


}