<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Author\Get;

use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Service\Author\AuthorFinder;

final class GetAuthorQueryHandler
{
    public function __construct(private AuthorFinder $finder)
    {
    }

    public function __invoke(GetAuthorQuery $query): Author
    {
        return $this->finder->execute(
            $query->authorId()
        );
    }
}
