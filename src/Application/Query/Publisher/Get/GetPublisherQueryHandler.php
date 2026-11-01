<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Publisher\Get;

use Colybri\Library\Domain\Model\Publisher\Publisher;
use Colybri\Library\Domain\Service\Publisher\PublisherFinder;

final class GetPublisherQueryHandler
{
    public function __construct(private PublisherFinder $finder)
    {
    }

    public function __invoke(GetPublisherQuery $query): Publisher
    {
        return $this->finder->execute(
            $query->publisherId()
        );
    }
}
