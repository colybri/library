<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Publisher\Get;

use Colybri\Library\Domain\Model\Publisher\Publisher;
use Colybri\Library\Domain\Service\Publisher\PublisherFinder;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class GetPublisherQueryHandler implements MessageHandlerInterface
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
