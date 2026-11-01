<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Edition\Get;

use Colybri\Library\Domain\Model\Edition\Edition;
use Colybri\Library\Domain\Service\Edition\EditionFinder;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GetEditionQueryHandler implements MessageHandlerInterface
{
    public function __construct(private EditionFinder $finder)
    {
    }

    public function __invoke(GetEditionQuery $query): Edition
    {
        return $this->finder->execute(
            $query->editionId()
        );
    }
}
