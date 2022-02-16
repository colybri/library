<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Author\Match;

use Colybri\Library\Domain\Service\Author\AuthorMatcher;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class MatchAuthorQueryHandler implements MessageHandlerInterface
{
    public function __construct(private AuthorMatcher $matcher)
    {
    }

    public function __invoke(MatchAuthorQuery $query): array
    {
        return $this->matcher->execute($query->match());
    }
}