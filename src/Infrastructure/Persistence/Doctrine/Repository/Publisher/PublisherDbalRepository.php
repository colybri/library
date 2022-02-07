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
            SELECT * from " . PublisherDbalMap::table() . " 
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
            INSERT into " . PublisherDbalMap::table() . " (
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

    private function map(array $author): Publisher
    {
        return Publisher::hydrate(
            Uuid::from($author['id']),
            PublisherName::from($author['name']),
            PublisherCity::from($author['city']),
            Uuid::from($author['country_id']),
            null === $author['foundation_year'] ? null : PublisherFoundationYear::from($author['foundation_year'])
        );

    }
}