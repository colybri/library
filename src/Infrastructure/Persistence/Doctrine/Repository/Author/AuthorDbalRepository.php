<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\Author;

use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Infrastructure\Adapter\Dbal\CriteriaDbalAdapter;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorFirstName;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorLastName;
use Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\DbalRepository;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\Author;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class AuthorDbalRepository extends DbalRepository implements AuthorRepository
{
    public function find(Uuid $id): ?Author
    {
        $sql = "
            SELECT * from authors 
             WHERE (
                id = :id
            );
        ";

        $query = $this->connectionRead->prepare($sql);
        $query->bindValue('id', $id->value());

        $author = $query->executeQuery()->fetchAssociative();

        if (false === $author) {
            return null;
        }

        return $this->map($author);
    }

    public function match(Criteria $criteria): array
    {
        $queryBuilder = $this->connectionRead->createQueryBuilder()
            ->select('*')->from(AuthorDbalMap::table());

        (new CriteriaDbalAdapter($queryBuilder, new AuthorDbalMap()))->build($criteria);

        $authors = $queryBuilder->executeQuery()->fetchAllAssociative();

        return \array_map(
            fn($author) => $this->map($author),
            $authors
        );
    }

    public function count(Criteria $criteria): int
    {
        $queryBuilder = $this->connectionRead->createQueryBuilder()
            ->select('*')->from(AuthorDbalMap::table());

        (new CriteriaDbalAdapter($queryBuilder, new AuthorDbalMap()))->build($criteria);

        return $queryBuilder->executeQuery()->rowCount();
    }

    public function insert(Author $author): void
    {
        $sql = "
            INSERT into authors (
                id, 
                first_name,
                last_name,
                country_id,                 
                is_pseudonym_of,
                born_year,
                death_year               
            ) VALUES (
                :id,
                :firstName,
                :lastName,
                :countryId,                      
                :isPseudonymOf,
                :bornAt,
                :deathAt
                      
            );
        ";

        $statement = $this->connectionWrite->prepare($sql);
        $statement->bindValue('id', $author->aggregateId()->value());
        $statement->bindValue('firstName', $author->firstName()->value());
        $statement->bindValue('lastName', $author->lastName()?->value());
        $statement->bindValue('countryId', $author->countryId()->value());
        $statement->bindValue('isPseudonymOf', $author->isPseudonymOf()?->value());
        $statement->bindValue('bornAt', $author->bornAt()->value());
        $statement->bindValue('deathAt', $author->deathAt()?->value());


        $statement->executeStatement();
    }

    public function update(Author $author): void
    {
        $sql = "
            UPDATE authors SET 
                first_name = :firstName,
                last_name = :lastName,
                country_id = :countryId,
                is_pseudonym_of = :isPseudonymOf,
                born_year = :bornAt,
                death_year = :deathAt,
                updated_at = CURRENT_TIMESTAMP 
             WHERE (
                id = :id
            );
        ";

        $statement = $this->connectionWrite->prepare($sql);

        $statement->bindValue('id', $author->aggregateId()->value());
        $statement->bindValue('firstName', $author->firstName()->value());
        $statement->bindValue('lastName', $author->lastName()?->value());
        $statement->bindValue('countryId', $author->countryId()->value());
        $statement->bindValue('isPseudonymOf', $author->isPseudonymOf()?->value());
        $statement->bindValue('bornAt', $author->bornAt()->value());
        $statement->bindValue('deathAt', $author->deathAt()?->value());


        $statement->executeStatement();
    }


    public function delete(Uuid $id): void
    {
        $sql = "
            DELETE from authors 
             WHERE (
                id = :id
            );
        ";

        $query = $this->connectionWrite->prepare($sql);
        $query->bindValue('id', $id->value());

        $query->executeStatement();
    }

    private function map(array $author): Author
    {
        return Author::reconstitute(
            Uuid::from($author['id']),
            AuthorFirstName::from($author['first_name']),
            null === $author['last_name'] ? null : AuthorLastName::from($author['last_name']),
            Uuid::from($author['country_id']),
            null === $author['is_pseudonym_of'] ? null : Uuid::from($author['is_pseudonym_of']),
            AuthorBornAt::from($author['born_year']),
            null === $author['death_year'] ? null : AuthorDeathAt::from($author['death_year'])
        );
    }
}
