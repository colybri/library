<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Author\Match;

use Colybri\Library\Application\Query\Shared\ListResponse;
use Colybri\Library\Domain\Service\Author\AuthorMatcher;

final class MatchAuthorQueryHandler
{
    public function __construct(private AuthorMatcher $matcher)
    {
    }

    public function __invoke(MatchAuthorQuery $query): ListResponse
    {
        return ListResponse::fromUnpaginatedList(
            $this->matcher->execute($query->match()),
            $query->offset(),
            $query->limit()
        );
    }
}
