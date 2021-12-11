<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Author;

use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorFirstName;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class AuthorCreator
{
    private AuthorRepository $repo;

    public function __construct(AuthorRepository $repo)
    {
        $this->repo = $repo;
    }

    public function execute(Uuid $uuid, AuthorFirstName $firstName): Author
    {
        //buscar por Uidd que no exista
        $author = Author::create($uuid, $firstName);
        $this->repo->insert($author);

        return $author;

    }
}