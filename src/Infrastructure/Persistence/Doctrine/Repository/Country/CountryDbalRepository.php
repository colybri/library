<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\Country;

use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Infrastructure\Adapter\Dbal\CriteriaDbalAdapter;
use Colybri\Library\Domain\Model\Country\Country;
use Colybri\Library\Domain\Model\Country\CountryRepository;
use Colybri\Library\Domain\Model\Country\ValueObject\CountryAlpha2Code;
use Colybri\Library\Domain\Model\Country\ValueObject\CountryName;
use Colybri\Library\Domain\Model\Country\ValueObject\CountryNationality;
use Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\DbalRepository;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class CountryDbalRepository extends DbalRepository implements CountryRepository
{
    public function find(Uuid $id): ?Country
    {
        $sql = "
            SELECT * from countries 
             WHERE (
                id = :id
            );
        ";

        $statement = $this->connectionRead->prepare($sql);
        $statement->bindValue('id', $id->value());

        $country = $statement->executeQuery();

        if (false === $country) {
            return null;
        }
        return $this->map($country->fetchAssociative());
    }

    public function match(Criteria $criteria): array
    {
        $queryBuilder = $this->connectionRead->createQueryBuilder()
            ->select('*')->from(CountryDbalMap::table());

        (new CriteriaDbalAdapter($queryBuilder, new CountryDbalMap()))->build($criteria);

        $countries = $queryBuilder->executeQuery()->fetchAllAssociative();

        return \array_map(
            fn($country) => $this->map($country),
            $countries
        );
    }

    public function count(Criteria $criteria): int
    {
        $queryBuilder = $this->connectionRead->createQueryBuilder()
            ->select('*')->from(CountryDbalMap::table());

        (new CriteriaDbalAdapter($queryBuilder, new CountryDbalMap()))->build($criteria);

        return $queryBuilder->executeQuery()->rowCount();
    }

    private function map(array $country): Country
    {
        return Country::hydrate(
            Uuid::from((string)$country['id']),
            CountryName::from((string)$country['en_short_name']),
            CountryAlpha2Code::from((string)$country['alpha_2_code']),
            CountryNationality::from((string)$country['nationality'])
        );
    }
}
