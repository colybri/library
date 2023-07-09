<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Fixtures\Country;

use Colybri\Library\Domain\Model\Country\Country;
use Colybri\Library\Domain\Model\Country\ValueObject\CountryAlpha2Code;
use Colybri\Library\Domain\Model\Country\ValueObject\CountryName;
use Colybri\Library\Domain\Model\Country\ValueObject\CountryNationality;
use Colybri\Library\Infrastructure\Fixtures\FixtureRepository;
use Doctrine\DBAL\Connection;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class CountryFixtures implements FixtureRepository
{
    public const COUNTRIES_IDS = [
        '3cfc35e3-84f5-4589-b164-d6bf50ff02bc',
        'a60c8d1c-17d0-43bb-8059-862f2265d2e5'
    ];
    private const NUM_CODE = [
        '3cfc35e3-84f5-4589-b164-d6bf50ff02bc' => 5789,
        'a60c8d1c-17d0-43bb-8059-862f2265d2e5' => 8957
    ];

    public function __construct(protected Connection $connectionWrite)
    {
    }

    public function load(): void
    {
        $entities = [
            Country::reconstitute(
                Uuid::from(self::COUNTRIES_IDS[0]),
                CountryName::from('Eden'),
                CountryAlpha2Code::from('ED'),
                CountryNationality::from('Edesians')
            ),
            Country::reconstitute(
                Uuid::from(self::COUNTRIES_IDS[1]),
                CountryName::from('Atlantis'),
                CountryAlpha2Code::from('AH'),
                CountryNationality::from('Atlanteans')
            )
        ];

        /** @var Country $entity */
        foreach ($entities as $entity) {

            $sql = "
                    INSERT into countries (
                        id, 
                        num_code,
                        en_short_name,
                        nationality,
                        alpha_2_code,
                        alpha_3_code
                    ) VALUES (
                        :id,
                        :num_code,
                        :name,
                        :nationality,
                        :code,
                        null
      
                    );
                ";

            $statement = $this->connectionWrite->prepare($sql);
            $statement->bindValue('id', $entity->aggregateId()->value());
            $statement->bindValue('code', $entity->alpha2Code()?->value());
            $statement->bindValue('num_code', self::NUM_CODE[$entity->aggregateId()->value()]);
            $statement->bindValue('name', $entity->name()->value());
            $statement->bindValue('nationality', $entity->nationality()->value());

            $statement->executeStatement();
        }
    }

    public function dependants(): array
    {
        return [];
    }

    public function clean(): void
    {
        foreach (self::COUNTRIES_IDS as $countryId) {

            $sql = "
                    DELETE from countries 
                     WHERE (
                        id = :id
                    );
                ";

            $query = $this->connectionWrite->prepare($sql);
            $query->bindValue('id', $countryId);

            $query->executeStatement();
        }
    }
}