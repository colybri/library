<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Author;

use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class AuthorDeleter
{

    public function __construct(private AuthorRepository $authorRepository, private AuthorFinder $finder)
    {
    }

    public function execute(Uuid $id): Author
    {
        $author = $this->finder->execute($id);

        $author->delete($this->authorRepository);

        return $author;
    }

}