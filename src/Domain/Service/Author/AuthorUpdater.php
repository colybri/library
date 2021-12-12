<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Author;

use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorFirstName;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorLastName;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class AuthorUpdater
{
    private AuthorRepository $repo;

    public function __construct(AuthorRepository $repo)
    {
        $this->repo = $repo;
    }

    public function execute(Uuid $id, AuthorFirstName $firstName, ?AuthorLastName $lastName, Uuid $countryId, ?Uuid $isPseudonymOf, AuthorBornAt $bornAt, ?AuthorDeathAt $deathAt): Author
    {

        $author = Author::create($id, $firstName, $lastName, $countryId, $isPseudonymOf, $bornAt, $deathAt);
        //¿?¿??
        $this->repo->update( $id, $firstName,  $lastName,  $countryId, $isPseudonymOf, $bornAt,  $deathAt);

        return $author;

    }
}