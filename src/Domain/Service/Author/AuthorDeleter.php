<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Author;

use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class AuthorDeleter
{

    public function __construct(private AuthorRepository $authorRepository, private AuthorFinder $finder)
    {
    }

    public function execute(Uuid $id): void
    {

        $this->ensureAuthorExist($id);

        $this->authorRepository->delete($id);
    }

    private function ensureAuthorExist(Uuid $id): void
    {
        $this->finder->execute($id);
    }
}