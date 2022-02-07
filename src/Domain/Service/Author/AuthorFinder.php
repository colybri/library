<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Author;

use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\Exception\AuthorDoesNotExistException;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class AuthorFinder
{
    public function __construct(private AuthorRepository $repo)
    {
    }

    /**
     * @throws AuthorDoesNotExistException
     */
    public function execute(Uuid $id): Author
    {
        $author = $this->repo->find($id);

        $this->ensureAuthorExist($author);

        return $author;
    }

    public function ensureAuthorExist(?Author $author): void
    {
        if (null === $author) {
            throw new AuthorDoesNotExistException(sprintf('Author whit id:%s does not exist on repository', $author));
        }
    }
}