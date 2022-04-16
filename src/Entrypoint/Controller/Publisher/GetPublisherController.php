<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Publisher;

use Colybri\Library\Application\Query\Publisher\Get\GetPublisherQuery;
use Colybri\Library\Entrypoint\Controller\QueryController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\Request;

class GetPublisherController extends QueryController
{
    public function __invoke(Request $request)
    {
        $publisherId = $request->attributes->get('id');

        $publisher = $this->ask(
            GetPublisherQuery::fromPayload(
                Uuid::v4(),
                [
                    GetPublisherQuery::PUBLISHER_ID_PAYLOAD => $publisherId,
                ]
            )
        );

        return $this->response($publisher);
    }
}
