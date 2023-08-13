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
        '09b87ec5-59d7-49a4-96ff-dcbfe92f5b43'
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
