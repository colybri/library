<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\Publisher;

use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Infrastructure\Adapter\Dbal\CriteriaDbalAdapter;
use Colybri\Library\Domain\Model\Publisher\Publisher;
use Colybri\Library\Domain\Model\Publisher\PublisherRepository;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherCity;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherFoundationYear;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherName;
use Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\DbalRepository;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class PublisherDbalRepository extends DbalRepository implements PublisherRepository
{
    public function find(Uuid $id): ?Publisher
    {
        $sql = "
            SELECT * from publishers 
             WHERE (
                id = :id
            );
        ";

        $query = $this->connectionRead->prepare($sql);
        $query->bindValue('id', $id->value());

        $publisher = $query->executeQuery()->fetchAssociative();

        if (false === $publisher) {
            return null;
        }

        return $this->map($publisher);
    }

    public function insert(Publisher $publisher): void
    {
        $sql = "
            INSERT into publishers (
                id, 
                name,
                city,
                country_id,                 
                foundation_year
            ) VALUES (
                :id,
                :name,
                :city,
                :countryId,                      
                :foundation
            );
        ";

        $statement = $this->connectionWrite->prepare($sql);
        $statement->bindValue('id', $publisher->aggregateId()->value());
        $statement->bindValue('name', $publisher->name()->value());
        $statement->bindValue('city', $publisher->city()?->value());
        $statement->bindValue('countryId', $publisher->countryId()->value());
        $statement->bindValue('foundation', $publisher->foundationYear()?->value());

        $statement->executeQuery();
    }

    public function match(Criteria $criteria): array
    {
        $queryBuilder = $this->connectionRead->createQueryBuilder()
            ->select('*')->from(PublisherDbalMap::table());

        (new CriteriaDbalAdapter($queryBuilder, new PublisherDbalMap()))->build($criteria);

        $publishers = $queryBuilder->executeQuery()->fetchAllAssociative();

        return \array_map(
            fn($publisher) => $this->map($publisher),
            $publishers
        );
    }

    public function update(Publisher $publisher): void
    {
        $sql = "
            UPDATE publishers SET 
                name = :name,
                city = :city,
                country_id = :countryId,
                foundation_year = :foundationYear,
                updated_at = CURRENT_TIMESTAMP 
             WHERE (
                id = :id
            );
        ";

        $statement = $this->connectionWrite->prepare($sql);

        $statement->bindValue('id', $publisher->aggregateId()->value());
        $statement->bindValue('name', $publisher->name()->value());
        $statement->bindValue('city', $publisher->city()?->value());
        $statement->bindValue('countryId', $publisher->countryId()->value());
        $statement->bindValue('foundationYear', $publisher->foundationYear()?->value());


        $statement->executeStatement();
    }

    public function count(Criteria $criteria): int
    {
        $queryBuilder = $this->connectionRead->createQueryBuilder()
            ->select('*')->from(PublisherDbalMap::table());

        (new CriteriaDbalAdapter($queryBuilder, new PublisherDbalMap()))->build($criteria);

        return $queryBuilder->executeQuery()->rowCount();
    }

    public function delete(Uuid $id): void
    {
        $sql = "
            DELETE from publishers 
             WHERE (
                id = :id
            );
        ";

        $query = $this->connectionWrite->prepare($sql);
        $query->bindValue('id', $id->value());

        $query->executeStatement();
    }

    private function map(array $publisher): Publisher
    {
        return Publisher::hydrate(
            Uuid::from($publisher['id']),
            PublisherName::from($publisher['name']),
            PublisherCity::from($publisher['city']),
            Uuid::from($publisher['country_id']),
            null === $publisher['foundation_year'] ? null : PublisherFoundationYear::from($publisher['foundation_year'])
        );
    }
}
