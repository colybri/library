<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Author;

use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\Exception\AuthorAlreadyExistException;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorName;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class AuthorCreator
{
    public function __construct(private AuthorRepository $repo)
    {
    }

    public function execute(Uuid $id, AuthorName $name, Uuid $countryId, ?Uuid $isPseudonymOf, AuthorBornAt $bornAt, ?AuthorDeathAt $deathAt): Author
    {
        $this->ensureAuthorDoesNonExist($id);

        $author = Author::create($id, $name, $countryId, $isPseudonymOf, $bornAt, $deathAt);
        $this->repo->insert($author);
        return $author;
    }

    public function ensureAuthorDoesNonExist(Uuid $id): void
    {
        if (null !== $this->repo->find($id)) {
            throw new AuthorAlreadyExistException(sprintf('Author whit id:%s already exist on repository', $id));
        }
    }
}
