<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Fixtures\Author;

use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorFirstName;
use Colybri\Library\Infrastructure\Fixtures\Country\CountryFixtures;
use Colybri\Library\Infrastructure\Fixtures\FixtureRepository;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class AuthorFixtures implements FixtureRepository
{
    public const AUTHOR_IDS = [
        '09b87ec5-59d7-49a4-96ff-dcbfe92f5b43',
        '2edcd2aa-bc93-44dd-baa2-8fa2ce2a3cc4',
        'f2d80e49-0e53-47c5-a0ed-cde60adc6c8a'
    ];

    public function __construct(private AuthorRepository $repository)
    {
    }

    public function load(): void
    {
        $entities = [
            Author::reconstitute(
                Uuid::from(self::AUTHOR_IDS[0]),
                AuthorFirstName::from('Autor ficticio'),
                null,
                Uuid::from(CountryFixtures::COUNTRIES_IDS[0]),
                null,
                AuthorBornAt::from(1900),
                AuthorDeathAt::from(1987)
            ),
            Author::reconstitute(
                Uuid::from(self::AUTHOR_IDS[1]),
                AuthorFirstName::from('Autor ficticio 2'),
                null,
                Uuid::from(CountryFixtures::COUNTRIES_IDS[1]),
                null,
                AuthorBornAt::from(1500),
                AuthorDeathAt::from(1878)
            ),
            Author::reconstitute(
                Uuid::from(self::AUTHOR_IDS[2]),
                AuthorFirstName::from('Autor ficticio 3'),
                null,
                Uuid::from(CountryFixtures::COUNTRIES_IDS[2]),
                null,
                AuthorBornAt::from(789),
                AuthorDeathAt::from(896)
            )

        ];

        foreach ($entities as $entity) {
            $this->repository->insert($entity);
        }
    }

    public function dependants(): array
    {
        return [
            \Colybri\Library\Infrastructure\Fixtures\Country\CountryFixtures::class
        ];
    }

    public function clean(): void
    {
        foreach (self::AUTHOR_IDS as $authorId) {
            $this->repository->delete(Uuid::from($authorId));
        }
    }
}
