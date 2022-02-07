<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Author;

use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorName;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class AuthorUpdater
{
    public function __construct(private AuthorRepository $repo, private AuthorFinder $finder)
    {
    }

    public function execute(Uuid $id, AuthorName $name, Uuid $countryId, ?Uuid $isPseudonymOf, AuthorBornAt $bornAt, ?AuthorDeathAt $deathAt): Author
    {
        $this->ensureAuthorExist($id);

        $author = Author::hydrate($id, $name, $countryId, $isPseudonymOf, $bornAt, $deathAt);

        $this->repo->update($author);

        return $author;

    }
    private function ensureAuthorExist(Uuid $id): void
    {
        $this->finder->execute($id);
    }
}